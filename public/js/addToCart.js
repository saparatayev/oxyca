$(document).ready(function(){
                
    $(".add-to-cart,.cart_quantity_up").click(function () {
        var idProd = $(this).attr("data-id");

        var $thisElem = $(this);

        $.ajax({
            url: "/cart/add/" + idProd,
            data: {
                id: idProd
            },
            type: "GET",
            dataType : "JSON",
            success: function(html) {
                $('#cart-count').html(html.cartCount);

                if($thisElem.attr("data-plus")) {
                    $thisElem.next().val(html.prodByIdCount);

                    $thisElem.parent().parent().next().html(
                        '<p class="cart_total_price">$' + html.oneProdTotalPrice + '</p>'
                    );

                    $('#totalPrice').html(html.totalPrice);
                }
                
            },
            error: function(e) {
                alert('error!');
            }
        })
            
        return false;
    });
    
});