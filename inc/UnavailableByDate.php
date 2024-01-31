<?php

class UnavailableByDate {

    const META_KEY_INFO   = 'unavailable_by_date';
    private static $wpdbObj;
    private static $varId = 0;

    public function __construct() {
        global $wpdb;
        self::$wpdbObj = $wpdb;
        self::createDB();

        add_action( 'stm_unavailable_by_date', array( $this, 'render' ) );
        add_action( 'save_post', array( $this, 'save' ), 15, 2 );
    }

    public static function table() {
        return self::$wpdbObj->prefix . 'unavailable_by_date';
    }

    private function createDB() {
        $charset_collate = self::$wpdbObj->get_charset_collate();
        $table_name      = self::table();

        if ( self::$wpdbObj->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {

            $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            variation_id INT NOT NULL,
            starttime INT NOT NULL,
            endtime INT NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
    }

    public static function getPeriods( $varId = '' ) {
        $id = ( ! empty( $varId ) ) ? $varId : stm_get_wpml_product_parent_id( get_the_ID() );

        $result = ( $id ) ? self::$wpdbObj->get_results( 'SELECT * FROM ' . self::table() . " WHERE `variation_id` = {$id} ORDER BY id ASC" ) : null;

        return $result;
    }

    public static function addPeriodIntoDB( $varId, $datestart, $dateend ) {
        $table_name = self::table();

        if ( ! is_null( self::checkEntry( $datestart, $dateend, $varId ) ) ) {
            self::$wpdbObj->update(
                $table_name,
                array(
                    'variation_id' => $varId,
                    'starttime'    => strtotime( $datestart ),
                    'endtime'      => strtotime( $dateend )
                ),
                array(
                    'variation_id' => $varId,
                    'starttime'    => strtotime( $datestart ),
                    'endtime'      => strtotime( $dateend ),
                ),
                array( '%d', '%d', '%d' ),
                array( '%d', '%d', '%d' )
            );
        } else {
            self::$wpdbObj->insert(
                $table_name,
                array(
                    'variation_id' => $varId,
                    'starttime'    => strtotime( $datestart ),
                    'endtime'      => strtotime( $dateend )
                ),
                array( '%d', '%d', '%d' )
            );
        }
    }

    public static function deleteEntry( $ids ) {
        $table_name = self::table();
        foreach ( explode( ',', $ids ) as $item ) {
            self::$wpdbObj->delete( $table_name, array( 'id' => $item ) );
        }
    }

    public static function checkEntry( $startTime, $endTime, $varId ) {
        $startTime = strtotime( $startTime );
        $endTime   = strtotime( $endTime );
        $result    = self::$wpdbObj->get_var( self::$wpdbObj->prepare( 'SELECT id FROM ' . self::table() . ' WHERE `variation_id` = %d AND `starttime` = %s AND `endtime` = %s', array( $varId, $startTime, $endTime ) ) );

        if ( ! empty( $result ) ) {
            return $result;
        }

        return null;
    }

    public static function save( $post_id, $post ) {
        if ( 'product' !== $post->post_type ) {
            return;
        }

        if ( isset( $_POST['remove-unavailable'] ) && ! empty( $_POST['remove-unavailable'] ) ) {
            self::deleteEntry( sanitize_text_field( $_POST['remove-unavailable'] ) );
        }

        if ( isset( $_POST['date-unavailable-start'] ) && isset( $_POST['date-unavailable-end'] )) {
            $start_date_count = count( $_POST['date-unavailable-start'] );
            for ( $q = 0; $q < $start_date_count; $q++ ) {
                if ( isset( $_POST['date-unavailable-start'][ $q ] )
                    && isset( $_POST['date-unavailable-end'][ $q ] )
                    && ! empty( $_POST['date-unavailable-start'][ $q ] )
                    && ! empty( $_POST['date-unavailable-end'][ $q ] )
                ) {
                    self::addPeriodIntoDB(
                        $post->ID,
                        wp_slash( $_POST['date-unavailable-start'][ $q ] ),
                        wp_slash( $_POST['date-unavailable-end'][ $q ] )
                    );
                }
            }
        }
    }

    public static function render(){
        $periods = self::getPeriods();
        $disabled = ( get_the_ID() !== stm_get_wpml_product_parent_id( get_the_ID() ) ) ? 'disabled="disabled"' : '';
    ?>
        <div class="rental-price-date-wrap" id="unavailable-tab-wrapper">
            <ul class="rental-price-date-list">
                <?php
                if ( ! empty( $periods ) ) :
                    foreach ( $periods as $k => $val ) :
                        ?>
                        <li>
                            <div class="repeat-number"><?php echo esc_html( $k + 1 ); ?></div>
                            <table>
                                <tr>
                                    <td>
                                        <?php echo esc_html__( 'Start Date', 'motors' ); ?>
                                    </td>
                                    <td>
                                        <input type="text" class="date-unavailable-start"
                                               value="<?php echo esc_html( gmdate( 'Y/m/d H:i', $val->starttime ) ); ?>"
                                               name="date-unavailable-start[]" <?php echo esc_attr( $disabled ); ?> autocomplete="off" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo esc_html__( 'End Date', 'motors' ); ?>
                                    </td>
                                    <td>
                                        <input type="text" class="date-unavailable-end"
                                               value="<?php echo esc_html( date( 'Y/m/d H:i', $val->endtime ) ); ?>"
                                               name="date-unavailable-end[]" <?php echo esc_attr( $disabled ); ?> autocomplete="off" />
                                    </td>
                                </tr>
                            </table>
                            <div class="btn-wrap">
                                <button class="remove-unavailable button-secondary"
                                        data-remove="<?php echo esc_attr( $val->id ); ?>" <?php echo esc_attr( $disabled ); ?>>
                                    <?php echo esc_html__( 'Remove', 'motors' ); ?>
                                </button>
                            </div>
                        </li>
                    <?php
                    endforeach;
                else :
                    ?>
                    <li>
                        <div class="repeat-number">1</div>
                        <table>
                            <tr>
                                <td>
                                    <?php echo esc_html__( 'Start Date', 'motors' ); ?>
                                </td>
                                <td>
                                    <input type="text" class="date-unavailable-start" name="date-unavailable-start[]" autocomplete="off" />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo esc_html__( 'End Date', 'motors' ); ?>
                                </td>
                                <td>
                                    <input type="text" class="date-unavailable-end" name="date-unavailable-end[]" autocomplete="off"/>
                                </td>
                            </tr>
                        </table>
                        <div class="btn-wrap">
                            <button class="remove-unavailable button-secondary">
                                <?php echo esc_html__( 'Remove', 'motors' ); ?>
                            </button>
                        </div>
                    </li>
                <?php
                endif;
                ?>
                <li>
                    <button type="button" class="repeat-unavailable button-primary button-large" <?php echo esc_attr( $disabled ); ?>>
                        <?php echo esc_html__( 'Add', 'motors' ); ?>
                    </button>
                </li>
            </ul>
            <input type="hidden" name="remove-unavailable"/>
        </div>
    <?php
    }

    public static function isDisableCar( $varId ) {
        return self::hasMatchedPeriods( $varId );
    }

    public static function hasMatchedPeriods( $varId ){
        $varId = ( ! empty( $varId ) ) ? $varId : stm_get_wpml_product_parent_id( get_the_ID() );
        $table_name      = self::table();
        $orderCookieData = stm_get_rental_order_fields_values();
        $startDate         = ( isset( $orderCookieData['pickup_date'] ) && ! empty( $orderCookieData['pickup_date'] ) ) ? strtotime( $orderCookieData['pickup_date'] ) : '';
        $endDate         = ( isset( $orderCookieData['return_date'] ) && ! empty( $orderCookieData['return_date'] ) ) ? strtotime( $orderCookieData['return_date'] ) : '';

        if(!empty($startDate) && !empty($endDate)) {
            $response = self::$wpdbObj->get_row(
                self::$wpdbObj->prepare(
                    "SELECT * FROM {$table_name}
					WHERE
					(variation_id = %d) AND
					(starttime BETWEEN %s AND %s OR
						endtime BETWEEN %s AND %s OR
						(starttime <= %s AND endtime >= %s)) ORDER BY id DESC LIMIT 1",
                    array( $varId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate )
                )
            );

            if(isset($response) && !empty($response)) {
                return true;
            }
        }

        return false;
    }

}

new UnavailableByDate();