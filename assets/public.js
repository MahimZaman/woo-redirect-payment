$ = jQuery ;

$(document).ready(function(){
    const minus = document.querySelector('.wss_minus')
    const plus = document.querySelector('.wss_plus')
    const qty = document.querySelector('.wss_quantity > input')

    if(minus){
        minus.addEventListener('click', function(){
            qty.stepDown();
            $(qty).change();
        })
    }

    if(plus){
        plus.addEventListener('click', function(){
            qty.stepUp();
            $(qty).change();
        })
    }
})