Muncipio = Muncipio || {};
Muncipio.Ajax = Muncipio.Ajax || {};

Muncipio.Ajax.ArchiveEvent = (function ($) {

    function Event() {
        this.init();
        this.$eventArchive = $('.event-archive');
    }

    Event.prototype.init = function() {
        var isEventPage = $('body').hasClass('post-type-archive-event');
        if(isEventPage){
            var $form = $('.archive-filters').find('form');
            var self = this;
            $form.submit(function(e){
                
                e.preventDefault();

                var getParams = $form.serialize();
                
                var data = {
                    archiveGet: getParams,
                    action : 'getRenderedArchivePosts'
                };
                $.ajax({
                    url: ajaxurl,
                    data: data,
                    type: 'get',
                    success : function(response) {
                        self.renderNewContent(response);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
        }
    }

    Event.prototype.renderNewContent = function(events) {

        if(events !== '[]'){
            this.$eventArchive.html(events);
        } else {
            console.log(events);
            console.log('empty response');
        }

    }

    return new Event();
    
})(jQuery);