(function ($) {
    $(document).ready(function () {
        setDatePicker();

        var timeOut = null;

        $('.stm-nav-link').on('click', function () {
            $('.stm-nav-link').removeClass('active');
            $(this).addClass('active');

            var tabId = $(this).data('id');

            $('.tab-pane.show').removeClass('show');
            $('#' + tabId).addClass('show');
        });

        $('body').on('click', '.repeat-fields', function (e) {
            e.preventDefault();
            $(this).parent().before(getRepeatView());

            setNum();

            if (timeOut != null) {
                clearTimeout(timeOut);
            }

            timeOut = setTimeout(function () {
                setDatePicker();
            }, 300);

        });

        $('body').on('click', '.repeat-days-fields', function (e) {
            e.preventDefault();
            $(this).parent().before(getRepeatDaysView());

            setNum();
        });

        $('body').on('click', '.repeat-fixed-price-fields', function (e) {
            e.preventDefault();
            $(this).parent().before(getRepeatFixedPriceView());

            setNum();
        });

        $('body').on('click', '.repeat-booking-length', function (e) {
            e.preventDefault();
            $(this).parent().before(getRepeatBookingLengthView());

            setNum();

            if (timeOut != null) {
                clearTimeout(timeOut);
            }

            timeOut = setTimeout(function () {
                setDatePicker();
            }, 300);

        });

        $('body').on('click', '.repeat-minimum-booking', function (e) {
            e.preventDefault();
            $(this).parent().before(getRepeatMinimumBookingView());

            setNumCustom('minimum-booking-tab-wrapper');

            if (timeOut != null) {
                clearTimeout(timeOut);
            }

            timeOut = setTimeout(function () {
                setDatePicker();
            }, 300);

        });

        $('body').on('click', '.repeat-unavailable', function (e) {
            e.preventDefault();
            $(this).parent().before(getRepeatUnavailableView());

            setNumCustom('unavailable-tab-wrapper');

            if (timeOut != null) {
                clearTimeout(timeOut);
            }

            timeOut = setTimeout(function () {
                setDatePicker();
            }, 300);

        });

        $('body').on('click', '.remove-fields', function (e) {
            e.preventDefault();

            var removeDate = $(this).data("remove");
            var val = $('input[name="remove-date"]').val();
            val = (val.length == 0) ? removeDate : val + ',' + removeDate;
            $('input[name="remove-date"]').val(val);
            $(this).parent().parent().remove();

            setNum();
        });

        $('body').on('click', '.remove-days-fields', function (e) {
            e.preventDefault();

            $(this).parent().parent().remove();

            setNum();
        });

        $('body').on('click', '.remove-booking-length', function (e) {
            e.preventDefault();

            var removeDate = $(this).data("remove");
            var val = $('input[name="remove-booking-length"]').val();
            val = (val.length == 0) ? removeDate : val + ',' + removeDate;
            $('input[name="remove-booking-length"]').val(val);
            $(this).parent().parent().remove();

            setNum();
        });

        $('body').on('click', '.remove-minimum-booking', function (e) {
            e.preventDefault();

            var removeDate = $(this).data("remove");
            var val = $('input[name="remove-minimum-booking"]').val();
            val = (val.length == 0) ? removeDate : val + ',' + removeDate;
            $('input[name="remove-minimum-booking"]').val(val);
            $(this).parent().parent().remove();

            setNumCustom('minimum-booking-tab-wrapper');
        });

        $('body').on('click', '.remove-unavailable', function (e) {
            e.preventDefault();

            var removeDate = $(this).data("remove");
            var val = $('input[name="remove-unavailable"]').val();
            val = (val.length == 0) ? removeDate : val + ',' + removeDate;
            $('input[name="remove-unavailable"]').val(val);
            $(this).parent().parent().remove();

            setNumCustom('unavailable-tab-wrapper');
        });



        if ($('select[id="product-type"]').val() == 'car_option') {
            $('#rental_price_for_date_repitor').hide();
            $('#discount_by_days_repitor').hide();
            $('#price_per_hour').hide();
        }

        $('select[id="product-type"]').on('change', function () {
            if ($(this).val() == 'car_option') {
                $('#rental_price_for_date_repitor').hide();
                $('#discount_by_days_repitor').hide();
                $('#price_per_hour').hide();
            } else {
                $('#rental_price_for_date_repitor').show();
                $('#discount_by_days_repitor').show();
                $('#price_per_hour').show();
            }
        });
    });

    function setDatePicker() {
        $('.date-pickup').stm_datetimepicker({
            closeOnDateSelect: true,
        });
        $('.date-drop').stm_datetimepicker({
            closeOnDateSelect: true,
        });
        $('.date-booking-length-pickup').stm_datetimepicker({
            closeOnDateSelect: true,
        });
        $('.date-booking-length-drop').stm_datetimepicker({
            closeOnDateSelect: true,
        });
        $('.date-minimum-booking-pickup').stm_datetimepicker({
            closeOnDateSelect: true,
        });
        $('.date-minimum-booking-drop').stm_datetimepicker({
            closeOnDateSelect: true,
        });
        $('.date-unavailable-start').stm_datetimepicker({
            closeOnDateSelect: true,
        });
        $('.date-unavailable-end').stm_datetimepicker({
            closeOnDateSelect: true,
        });
    }

    function setNum() {
        var i = 1;
        $('.repeat-number').each(function () {
            $(this).text(i);
            i++;
        });

        i = 1;
        $('.repeat-days-number').each(function () {
            $(this).text(i);
            i++;
        });
    }

    function setNumCustom(parent_id) {
        var i = 1;
        $('#' + parent_id).find('.repeat-number').each(function () {
            $(this).text(i);
            i++;
        });

        i = 1;
        $('#' + parent_id).find('.repeat-days-number').each(function () {
            $(this).text(i);
            i++;
        });
    }

    function getRepeatView() {
        var view = '<li>\n' +
            '                <div class="repeat-number">1</div>\n' +
            '                <table>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Pickup Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-pickup" name="date-pickup[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Drop Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-drop" name="date-drop[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Price\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="number" min="0.01" step="0.01" name="date-price[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                </table>\n' +
            '                <div class="btn-wrap">\n' +
            '                    <button class="remove-fields button-secondary">Remove</button>\n' +
            '                </div>\n' +
            '            </li>';

        return view;
    }

    function getRepeatBookingLengthView() {
        var view = '<li>\n' +
            '                <div class="repeat-number">1</div>\n' +
            '                <table>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Pickup Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-booking-length-pickup" name="date-booking-length-pickup[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Drop Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-booking-length-drop" name="date-booking-length-drop[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Price\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="number" min="0.01" step="0.01" name="date-booking-length-price[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Days >=\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="number" min="1" step="1" name="length[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                </table>\n' +
            '                <div class="btn-wrap">\n' +
            '                    <button class="remove-booking-length button-secondary">Remove</button>\n' +
            '                </div>\n' +
            '            </li>';

        return view;
    }

    function getRepeatMinimumBookingView() {
        var view = '<li>\n' +
            '                <div class="repeat-number">1</div>\n' +
            '                <table>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Pickup Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-minimum-booking-pickup" name="date-minimum-booking-pickup[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Drop Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-minimum-booking-drop" name="date-minimum-booking-drop[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Days >=\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="number" min="1" step="1" name="minimum-booking-length[]" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                </table>\n' +
            '                <div class="btn-wrap">\n' +
            '                    <button class="remove-minimum-booking button-secondary">Remove</button>\n' +
            '                </div>\n' +
            '            </li>';

        return view;
    }

    function getRepeatDaysView() {
        var view = '<li>\n' +
            '                        <div class="repeat-days-number">1</div>\n' +
            '                        <table>\n' +
            '                            <tr>\n' +
            '                                <td>\n' +
            '                                    Days\n' +
            '                                </td>\n' +
            '                                <td>\n' +
            '                                    <input type="number" name="days[]" />\n' +
            '                                </td>\n' +
            '                                <td>>=</td>\n' +
            '                            </tr>\n' +
            '                            <tr>\n' +
            '                                <td>\n' +
            '                                    Discount\n' +
            '                                </td>\n' +
            '                                <td>\n' +
            '                                    <input type="number" name="percent[]" />\n' +
            '                                </td>\n' +
            '                                <td>\n' +
            '                                    %\n' +
            '                                </td>\n' +
            '                            </tr>\n' +
            '                        </table>\n' +
            '                        <div class="btn-wrap">\n' +
            '                            <button class="remove-days-fields button-secondary">Remove</button>\n' +
            '                        </div>\n' +
            '                    </li>';

        return view;
    }

    function getRepeatFixedPriceView() {
        var view = '<li>\n' +
            '                        <div class="repeat-days-number">1</div>\n' +
            '                        <table>\n' +
            '                            <tr>\n' +
            '                                <td>\n' +
            '                                    Days\n' +
            '                                </td>\n' +
            '                                <td>\n' +
            '                                    <input type="number" min="1" name="pfd_days[]" />\n' +
            '                                </td>\n' +
            '                                <td>>=</td>\n' +
            '                            </tr>\n' +
            '                            <tr>\n' +
            '                                <td>\n' +
            '                                    Price\n' +
            '                                </td>\n' +
            '                                <td>\n' +
            '                                    <input type="number" min="0.01" step="0.01"  name="pfd_price[]" />\n' +
            '                                </td>\n' +
            '                                <td>\n' +
            '                                </td>\n' +
            '                            </tr>\n' +
            '                        </table>\n' +
            '                        <div class="btn-wrap">\n' +
            '                            <button class="remove-days-fields button-secondary">Remove</button>\n' +
            '                        </div>\n' +
            '                    </li>';

        return view;
    }


    function getRepeatUnavailableView(){
        var view = '<li>\n' +
            '                <div class="repeat-number">1</div>\n' +
            '                <table>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            Start Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-unavailable-start" name="date-unavailable-start[]" autocomplete="off" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                    <tr>\n' +
            '                        <td>\n' +
            '                            End Date\n' +
            '                        </td>\n' +
            '                        <td>\n' +
            '                            <input type="text" class="date-unavailable-end" name="date-unavailable-end[]" autocomplete="off" />\n' +
            '                        </td>\n' +
            '                    </tr>\n' +
            '                </table>\n' +
            '                <div class="btn-wrap">\n' +
            '                    <button class="remove-unavailable button-secondary">Remove</button>\n' +
            '                </div>\n' +
            '            </li>';
        return view;
    }

})(jQuery);
