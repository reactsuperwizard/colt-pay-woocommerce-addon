jQuery('.colt-pay-button').on('click', function(e){
	console.log('start');
	e.preventDefault();
	var currency = jQuery(this).data('currency');
	var amount = jQuery(this).data('amount');
	var loader = jQuery(this).after( "<div class='coltpay-loader'></div>" );
	jQuery.ajax({
        type: "post",
        url: ajax_url,
        data: {action:"coltpay_button", currency: currency, amount, amount},
        success: function (res) {
        	jQuery('.coltpay-loader').remove();
            if (res.result.status == "OK") {
            	window.location.href = res.result.redirect;
            } else {
                alert("something is wrong");
            }
        }
    });
});