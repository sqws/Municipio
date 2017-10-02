Muncipio = Muncipio || {};
Muncipio.Ajax = Muncipio.Ajax || {};

Muncipio.Ajax.ArchiveEvent = (function ($) {

    function Event() {
        this.init();
    }

    Event.prototype.init = function() {
        var isEventPage = $('body').hasClass('post-type-archive-event');
        if(isEventPage){
            var $form = $('.archive-filters').find('form');
            var self = this;
            $form.submit(function(e){
                
                e.preventDefault();

                var getParams = $form.serialize();
                
                console.log(getParams);

                var text = self.getParameterByName('s', getParams);
                var from = self.getParameterByName('from', getParams);
                var to = self.getParameterByName('to', getParams);
                var filter = self.getParameterByName('filter', getParams);

                var params = {
                    text : text,
                    from : from,
                    to : to,
                    filter : filter
                };

                console.log(filter);
                
                var data = {
                    archiveGet: params,
                    action : 'getRenderedArchivePosts'
                };
                $.ajax({
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    type: 'get',
                    success : function(response) {
                        console.log(response);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
        }
    }

    Event.prototype.getParameterByName = function(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    return new Event();
    
})(jQuery);