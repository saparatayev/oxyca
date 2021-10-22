$(document).ready(function(){
                
    $(".cart_quantity_down").click(function () {
        var idProd = $(this).attr("data-id");

        var $thisElem = $(this);

        $.ajax({
            url: "/cart/subtract/" + idProd,
            data: {
                id: idProd
            },
            type: "GET",
            dataType : "JSON",
            success: function(html) {
                $('#cart-count').html(html.cartCount);
                $('#totalPrice').html(html.totalPrice);

                if(html.prodByIdCount > 0) {
                    $thisElem.prev().val(html.prodByIdCount);

                    $thisElem.parent().parent().next().html(
                        '<p class="cart_total_price">$' + html.oneProdTotalPrice + '</p>'
                    );
                } else {
                    $thisElem.parent().parent().parent().remove();
                }   
            },
            error: function(e) {
                alert('error!');
            }
        })
            
        return false;
    });
    
});