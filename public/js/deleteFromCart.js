$(document).ready(function(){
                
    $(".cart_quantity_delete").click(function () {
        var $myElem = $(this);

        var idProd = $(this).attr("data-id");
        $.ajax({
            url: "/cart/delete/" + idProd,
            data: {
                id: idProd
            },
            type: "GET",
            dataType : "JSON",
            success: function(html) {
                $('#cart-count').html(html.cartCount);
                $('#totalPrice').html(html.totalPrice);

                $myElem.parent().parent().remove();
            },
            error: function(e) {
                alert('error!');
                console.log(e.message);
            }
        })
            
        return false;
    });
    
});