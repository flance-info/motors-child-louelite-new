<?php
$taxes = stm_rental_order_taxes();


$cartfees = WC()->cart->get_fees();

$fields = stm_get_rental_order_fields_values();

if(!empty($cartfees)) {
    $fees = [];

    foreach($cartfees as $key => $fee) {
        $current_fee = get_page_by_title($fee->name, OBJECT, 'waef');

        if (get_locale() == 'fr_FR') {
            $fee_label = get_field('fee_french_title', $current_fee->ID);
        } else {
            $fee_label = $fee->name;
        }

        $fee_tax = 0;

        if($fee->taxable == 1) {
            $fee_tax = $fee->tax * $fields['order_days'];
        }

        // add fees to tax list
        $fees[$key] = [
            'label' => $fee_label,
            'value' => wc_price($fee->total * $fields['order_days'] + $fee_tax),
        ];
    }

    $taxes = $fees + $taxes;
}

$order_id  = isset($wp->query_vars['order-received']) ? absint( $wp->query_vars['order-received'] ) : 0;

if($order_id > 0) {
    $the_order = wc_get_order( $order_id );
    foreach( $the_order->get_items('fee') as $item_id => $item_fee ){

        $taxes[$item_id] = [
            'label' => $item_fee->get_name(),
            'value' => wc_price($item_fee->get_total() * $fields['order_days']),
        ];

    }
}

if(!empty($taxes)): ?>
    <div class="stm_rent_table stm_rent_tax_table">
        <div class="heading heading-font"><h4><?php esc_html_e('Taxes & Fees', 'motors'); ?></h4></div>
        <table>
            <tbody>
                <tr>
                    <td colspan="3" class="divider"></td>
                </tr>
                <?php foreach ( $taxes as $name => $tax ) : ?>
                    <tr class="cart-tax tax-<?php echo sanitize_title( $name ); ?>">
                        <td><?php echo sanitize_text_field($tax['label']); ?></td>
                        <td>&nbsp;</td>
                        <td><?php echo sanitize_text_field($tax['value']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="divider"></td>
                </tr>
            </tbody>
        </table>
    </div>
<?php endif; ?>
