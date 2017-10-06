Muncipio = Muncipio || {};
Muncipio.Ajax = Muncipio.Ajax || {};

Muncipio.Ajax.ArchiveEvent = (function ($) {

    function Event() {
        this.$eventArchive = $('.event-archive');
        this.isEventPage = $('body').hasClass('post-type-archive-event');
        this.params = '';
        this.$form = {};
        this.$loadMoreBtn = {};
        this.page = 1;
        this.init();
    }

    Event.prototype.init = function() {
        if(this.isEventPage){
            $('.type-list').hide();
            $('.type-loadmore').show();
            this.actions();
        }   
    }

    Event.prototype.actions = function() {
        this.$form = $('.archive-filters').find('form');
        this.$loadMoreBtn = $('.type-loadmore').find('.o-button');
        var self = this;

        this.$form.submit(function(e) {
            
            e.preventDefault();

            self.params = self.$form.serialize();

            var data = {
                archiveGet: self.params,
                action : 'getRenderedArchivePosts'
            };
    
            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'get',
                success : function(response) {
                    var data = JSON.parse(response);
                    self.renderNew(data.items);
                    self.checkLoadMore(data.pagesLeft);
                },
                error: function (error) {
                    console.log(error);
                    return false;
                }
            });

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

            var data = {
                archiveGet: self.params,
                action : 'getRenderedArchivePosts'
            };
    
            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'get',
                success : function(response) {
                    var data = JSON.parse(response);
                    self.renderMore(data.items);
                    self.checkLoadMore(data.pagesLeft);
                },
                error: function (error) {
                    console.log(error);
                    return false;
                }
            });
            
        });
    }

    Event.prototype.checkLoadMore = function(pagesLeft){
        if(pagesLeft == 0){
            $('.type-loadmore').hide();
        } else {
            $('.type-loadmore').show();
        }
    }

    Event.prototype.renderNew = function(events) {
        var $noEvents = $('.no-events');
        if(events !== ''){
            $noEvents.hide();
            this.$eventArchive.html(events);
        } else {
            $noEvents.show();
            $('.type-loadmore').hide();
            this.$eventArchive.hide();
        }

    }

    Event.prototype.renderMore = function(events) {
        var $noEvents = $('.no-events');
        if(events !== ''){
            $noEvents.hide();
            this.$eventArchive.append(events);
        } else {
            $('.type-loadmore').hide();
            this.$eventArchive.hide();
        }

    }

    return new Event();
    
})(jQuery);