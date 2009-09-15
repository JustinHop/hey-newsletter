
(function($) {
heyNews = { 

    init : function() {
        $("#newsletter-search-input").datepicker({ dateFormat: 'yy-mm-dd',
            showOn: 'button', buttonImage: '/wp-admin/images/calendar.gif', buttonImageOnly: true, showButtonPanel: true}); 

        $("#newsletter_date_value").datepicker({ dateFormat: 'yy-mm-dd',
            showOn: 'button', buttonImage: '/wp-admin/images/calendar.gif', buttonImageOnly: true, showButtonPanel: true}); 

        $("tbody").sortable({
            update: function(event, ui) {
            //renumber();
            },
            //handle: 'th'
            items: 'tr'
            });

        $(".heypostadd").click(function () { 
            $(".heypost:first").clone(true).appendTo("#heyposts"); 
            renumber();
            });

        $(".heypostremove").click(function () { 
            $(this).parent().slideUp(); 
            $(this).parent().remove(); 
            renumber();
            });
    },


renumber : function (){
    $(".heypost").each(function(i){
            var newid = "heypost_" + i.toString();
            $j(this).attr("id", newid );
            });

    $(".heypost > textarea").each(function(i){
            var newid = "heypostpost_" + i.toString();
            $j(this).attr("name", newid );
            });

    $(".heypost > select").each(function(i){
            var newid = "heypostcats_" + i.toString() + "[]";
            $j(this).attr("name", newid );
            });
}



};

$(document).ready(function(){heyNews.init();});
})(jQuery);

