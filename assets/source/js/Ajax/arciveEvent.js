Muncipio = Muncipio || {};
Muncipio.Ajax = Muncipio.Ajax || {};

Muncipio.Ajax.ArchiveEvent = (function ($) {

    function Event() {
        this.isEventPage = $('body').hasClass('post-type-archive-event');
        this.$eventArchive = {};
        this.params = '';
        this.$form = {};
        this.$loadMoreBtn = {};
        this.page = 1;
        this.init();
    }

    Event.prototype.init = function() {
        if(this.isEventPage){
            this.$eventArchive = $('.event-archive');
            $('.type-list').hide();
            $('.type-loadmore').show();
            this.actions();
        }   
    }

    Event.prototype.actions = function() {
        this.$filters = $('.dropdown-event_categories').find('li');
        this.$loadMoreBtn = $('.type-loadmore').find('.o-button');
        this.$form = $('.archive-filters').find('form');
        var self = this;

        this.$form.submit(function(e) {
            e.preventDefault();
            self.setEvents('new');
        });

        this.$form.on('fetchNewEvents', function(e) {
            self.setEvents('new');
        });

        this.$loadMoreBtn.click(function(e) {
            e.preventDefault();
            var oldPage = self.page;
            self.page = self.page + 1;

            var newPage = '&page=' + self.page;

            if(self.params.indexOf('&page=') !== -1){
                self.params = self.params.replace('&page=' + oldPage, newPage);
            } else {
                self.params = self.params + '&page=' + self.page;
            }

            self.setEvents('append');
        });
    }

    Event.prototype.setEvents = function(action) {
        var self = this;
        this.params = this.$form.serialize();

        var data = {
            archiveGet: self.params,
            action : 'getRenderedArchivePosts'
        };

        this.$eventArchive.addClass('loading');

        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
        
                // Upload progress
                xhr.upload.addEventListener("progress", function(evt){
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        //Do something with upload progress
                        console.log(percentComplete);
                    }
               }, false);
        
               // Download progress
               xhr.addEventListener("progress", function(evt){
                   if (evt.lengthComputable) {
                       var percentComplete = evt.loaded / evt.total;
                       // Do something with download progress
                       console.log(percentComplete);
                   }
               }, false);
        
               return xhr;
            },
            url: ajaxurl,
            data: data,
            type: 'get',
            success : function(response) {
                var data = JSON.parse(response);
                self.render(action, data.items);
                self.checkLoadMore(data.pagesLeft);
                self.$eventArchive.removeClass('loading');
            },
            error: function (error) {
                console.log(error);
                return false;
            }
        });
    }

    Event.prototype.checkLoadMore = function(pagesLeft){
        if(pagesLeft == 0){
            $('.type-loadmore').hide();
        } else {
            $('.type-loadmore').show();
        }
    }

    Event.prototype.render = function(action, events) {
        if(action === 'new'){
            var $noEvents = $('.no-events');
            if(events !== ''){
                $noEvents.hide();
                this.$eventArchive.html(events);
            } else {
                $noEvents.show();
                $('.type-loadmore').hide();
                this.$eventArchive.hide();
            }
            var $noEvents = $('.no-events');
            if(events !== ''){
                $noEvents.hide();
                this.$eventArchive.append(events);
            } else {
                $('.type-loadmore').hide();
                this.$eventArchive.hide();
            }
        } else if(action === 'append'){
            var $noEvents = $('.no-events');
            if(events !== ''){
                $noEvents.hide();
                this.$eventArchive.append(events);
            } else {
                $('.type-loadmore').hide();
                this.$eventArchive.hide();
            }
        }

    }

    return new Event();
    
})(jQuery);