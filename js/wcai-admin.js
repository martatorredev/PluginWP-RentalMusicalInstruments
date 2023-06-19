(function(a){a(document).ready(function(){a("input#_alquilable").change(
    function(){
	     a(this).is(":checked")?a(".show_if_alquilable").show():(a(".show_if_alquilable").hide(),
a("input.variation_is_alquilable").attr("checked",!1).change());a(".alquiler_tab").is(".active")&&a("ul.wc-tabs li:visible").eq(0).find("a").click()}).change();
})})(jQuery);

jQuery(document).ready(function($){
    $('.my-color-field').wpColorPicker();
});

