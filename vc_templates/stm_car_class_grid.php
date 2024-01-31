<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);

$css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class($css, ' '));

if (empty($posts_per_page)) {
    $posts_per_page = 9;
}

$args = array(
    'post_type' => 'product',
    'posts_per_page' => intval($posts_per_page),
    'post_status' => 'publish',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_type',
            'field' => 'slug',
            'terms' => 'car_option',
            'operator' => 'NOT IN'
        )
    ),
);

if($display_rent_cars && !$display_simple_cars){

    $args['meta_key'] = 'in_rental_car_de';
    $args['meta_compare'] = 'EXISTS';


}elseif(!$display_rent_cars && $display_simple_cars){

    $args['meta_key'] = 'in_rental_car_de';
    $args['meta_compare'] = 'NOT EXISTS';

}


$offices = new WP_Query($args);


if ($offices->have_posts()): ?>
    <div class="stm_products_grid_class">
        <?php while ($offices->have_posts()): $offices->the_post();
            $id = get_the_ID();
            $is_variation = get_post_meta(get_the_ID(), 'in_rental_car_de', true);

            $is_variation = !empty($is_variation) ? true : false;

            $s_title = get_post_meta($id, 'cars_info', true);

            $car_info = stm_get_car_rent_info($id);

            $price = stm_getDefaultVariablePrice($id, 0, true);

            ?>
<!-- <?php //echo esc_url(stm_woo_shop_page_url() . esc_attr('#product-' . get_the_ID())); ?> -->
            <div class="stm_product_grid_single" style="position: relative;">

                <div class="inner">
                    <div class="stm_top clearfix">
                        <div class="stm_left heading-font">
                            <h3><?php the_title(); ?></h3>
                            <?php if (!empty($s_title)): ?>
                                <div class="s_title"><?php echo sanitize_text_field($s_title); ?></div>
                            <?php endif; ?>

                        </div>
                        <?php if (!empty($car_info)): ?>
                            <div class="stm_right">
                                <?php foreach ($car_info as $slug => $info):
                                    $name = $info['value'];
                                    if ($info['numeric']) {
                                        $name = $info['value'] . ' ' . esc_html__($info['name'], 'motors');
                                    }
                                    $font = $info['font'];
                                    ?>
                                    <div class="single_info stm_single_info_font_<?php echo esc_attr($font) ?>">
                                        <i class="<?php echo esc_attr($font); ?>"></i>
                                        <span><?php echo sanitize_text_field($name); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (has_post_thumbnail()): ?>
                        <div class="stm_image" style="    margin-bottom: 65px;">
                            <?php the_post_thumbnail('stm-img-796-466', array('class' => 'img-responsive')); ?>
                        </div>
                    <?php endif; ?>


                    <div class="stm-inner-product-content_de">

                    <?php
                    $title = get_theme_mod('stm_add_lauto_title');
                    $tel = get_theme_mod('stm_add_lauto_phone');
                    $email = get_theme_mod('stm_add_lauto_email');

                    if($is_variation && !empty($price)){ ?>


                        <?php
                        if(!empty($title)){
                            ?>
                            <div class="heading-font"><h5><?php echo $title;?></h5></div>
                            <?php
                        }
                        ?>


                        <a href="<?php echo esc_url(stm_woo_shop_page_url() . esc_attr('#product-' . get_the_ID())); ?>"
                           class="button stm-button stm-button-icon stm-button-secondary-color"
                           style="background-color:#3bd100;
                           color:#000000!important;
                           box-shadow: 0 2px 0 rgba(59,209,0,0.8)"
                           title="<?php _e('Book Now', 'motors');?>"><i class="fa fa-car"></i><?php _e('Book Now', 'motors');?>
                            </a>


                        <?php

                    }else{

                        if(!empty($title)){
                        ?>
                        <div class="heading-font"><h5><?php echo $title;?></h5></div>
                        <?php
                        }

                        if(!empty($tel)){
                            ?>
                            <a class="button stm-button stm-button-icon stm-button-secondary-color "
                               style="background-color:#3bd100;
                           color:#000000!important;
                           box-shadow: 0 2px 0 rgba(59,209,0,0.8)"
                               href="tel:<?php echo $tel;?>"
                               title="<?php _e('Call', 'motors');?> "><i class="fa fa-phone"></i><?php _e('Call', 'motors');?>
                            </a>

                            <?php
                        }

                        if(!empty($email)){
                            ?>
                            <a class="button stm-button stm-button-icon stm-button-secondary-color "
                               style="background-color:#3bd100; color:#000000!important;box-shadow: 0 2px 0 rgba(59,209,0,0.8)"
                               href="mailto:<?php echo $email;?>"
                               title="<?php _e('E-mail', 'motors');?>"><i class="fa fa-at"></i><?php _e('E-mail', 'motors');?>
                            </a>
                            <?php
                        }

                    }

                    ?>
                        </div>

                    </div>
            </div>
        <?php endwhile; ?>

        <?php

        if($offices->found_posts > $posts_per_page){

            ?>
            <div class="text-center" style="
                width: 100%;
            ">
                <a class="button stm-button stm-button-icon stm-button-secondary-color "
                   style="background-color:#3bd100; color:#000000!important;box-shadow: 0 2px 0 rgba(59,209,0,0.8)"
                   href="<?php echo esc_url(stm_woo_shop_page_url());?>">
                    <i class="fa fa-car"></i> <?php _e('See more', 'motors');?>
                </a>
            </div>
            <?php
        }

        ?>

    </div>
    <?php
    wp_reset_postdata();

endif; ?>
