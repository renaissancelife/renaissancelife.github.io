/* 设置rem */
/*(function(doc, win) {
    var docEl = doc.documentElement
    var resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize'
    var recalc = function() {
        var clientWidth = docEl.clientWidth
        clientWidth = clientWidth > 960 ? 960 : clientWidth
        if (!clientWidth) return
        var _fontSize = (clientWidth * 20 / 375) > 22 ? '22.08' : (clientWidth * 20 / 375);
        docEl.style.fontSize = _fontSize + 'px'
    }
    if (!doc.addEventListener) return
    win.addEventListener(resizeEvt, recalc, false)
    doc.addEventListener('DOMContentLoaded', recalc, false)
})(document, window);*/
window.TalMain = {
    init: function () {
        var _ts = this,
            ua = navigator.userAgent.toLowerCase();
        if ($('.zk_audio').length > 0) {
            if (typeof window.touch == 'undefined') {
                _ts.tools.loadJS('https://zkres.myzaker.com/static/zaker_ui/new/zk_mod/tpl/assets/js/libs/touchjs.min.js', function () {
                    _ts.zkAudio.init();
                });
            } else {
                _ts.zkAudio.init();
            }
        }
        if ($('.zk_titbg').length > 0) {
            _ts.zkTitBg();
        }
        if ($('.zk_vote')) {
            if (typeof $.cookie == 'undefined') {
                _ts.tools.loadJS('https://zkres.myzaker.com/static/zaker_ui/new/zk_mod/tpl/assets/js/libs/jquery-cookie.js?v=3', function () {
                    _ts.zkVote.init();
                });
            } else {
                _ts.zkVote.init();
            }
        }
        if ($('.zk_scrollimg').length > 0) {
            _ts.tools.loadJS('http://121.9.213.58/zkres.myzaker.com/static/zaker_ui/new/zk_mod/tpl/assets/js/libs/iscroll.js', function () {
                _ts.zkScrollImg.init();
            });
        }
        if ($('.zk_upvideo').length > 0) {
            if (typeof window.touch == 'undefined') {
                // _ts.tools.loadJS('https://cdn.bootcss.com/vConsole/3.3.0/vconsole.min.js', function() {
                //     new VConsole()
                // })
                _ts.tools.loadJS('https://zkres.myzaker.com/static/zaker_ui/new/zk_mod/tpl/assets/js/libs/touchjs.min.js', function () {
                    _ts.zkUpVideo.init();
                });
            } else {
                _ts.zkUpVideo.init();
            }
        }
        //不在zaker、百度小程序、支付宝小程序中，ID_video视频添加拉新逻辑
        if($('#ID_video').length == 1 && !(ua.match(/zaker/ig) || ua.match(/swan\//ig) || ua.match(/alipayclient/ig))){
            _ts.IDvideo();
        }
    },
    config: {
        pageData: (function () {
            var pageData = {};
            var isKey = false;
            var paramArr = window.location.search.substr(1).split('&');
            for (var i = 0; i < paramArr.length; i++) {
                if (paramArr[i].match(/(.+?)=(.+?)/ig)) {
                    var curArr = paramArr[i].split('=');
                    try {
                        pageData[curArr[0]] = decodeURIComponent(curArr[1]);
                    } catch (err) {
                        pageData[curArr[0]] = curArr[1];
                    }
                    isKey = true;
                }
            }
            return isKey ? pageData : null;
        })()
    },
    tools: {
        getUrlName: function (name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
            var r = window.location.search.substr(1).match(reg);
            if (r !== null) return unescape(r[2]);
            return null;
        },
        loadJS: function (url, success) {
            var domScript = document.createElement('script');
            domScript.src = url;
            success = success || function () { };
            if (navigator.userAgent.indexOf("MSIE") > 0) {
                domScript.onreadystatechange = function () {
                    if ('loaded' === this.readyState || 'complete' === this.readyState) {
                        success();
                        this.onload = this.onreadystatechange = null;
                        this.parentNode.removeChild(this);
                    }
                }
            } else {
                domScript.onload = function () {
                    success();
                    this.onload = this.onreadystatechange = null;
                    this.parentNode.removeChild(this);
                }
            }
            document.getElementsByTagName('head')[0].appendChild(domScript);
        },
        assign: function (target) {
            if (target === null) {
                throw new TypeError('Cannot convert undefined or null to object')
            }
            target = Object(target)
            for (var index = 1; index < arguments.length; index++) {
                var source = arguments[index]
                if (source !== null) {
                    for (var key in source) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) {
                            target[key] = source[key]
                        }
                    }
                }
            }
            return target
        },
        appType: function () {
            //wx, zaker, other
            var ua = navigator.userAgent.toLowerCase(),
                _uid = TalMain.tools.getUrlName('_uid'),
                _udid = TalMain.tools.getUrlName('_udid');
            return (ua.match(/MicroMessenger/i) == "micromessenger") ? 'wx' : (ua.match(/zaker/i) == 'zaker' || _uid || _udid) ? 'zaker' : 'other';
        },
        platform: (navigator.userAgent.toLowerCase()).match(/iphone|ipod|ipad/ig) ? 'ios' : 'android',
        getIosVer: function () {
            var str = navigator.userAgent.toLowerCase();
            var ver = str.match(/cpu iphone os (.*?) like mac os/);
            if (!ver) {
                return null
            } else {
                return (ver[1].replace(/_/g, "."));
            }
        },
        getAndroidVer: function() {
            var ua = navigator.userAgent.toLowerCase();
            var version = null;
            if (ua.indexOf("android") > 0) {
                var reg = /android [\d._]+/gi;
                var v_info = ua.match(reg);
                version = (v_info + "").replace(/[^0-9|_.]/ig, "").replace(/_/ig, "."); //得到版本号4.2.2
                version = parseInt(version.split('.')[0]);// 得到版本号第一位
            }

            return version;
        }
    },
    isJQReady: function (fun) {
        var tryToReady = function () {
            if (window.$) {//执行回调
                if (typeof fun === 'function') {//$.isFunction(fun)
                    try {
                        fun();
                    } catch (e) { }
                }
                return;
            } else {
                setTimeout(function () {
                    tryToReady(fun);
                }, 500);
            }
        };
        tryToReady();
    },
    IDvideo: function(){
        var _ts = this,
            ua = navigator.userAgent.toLowerCase(),
            molg = function(data){
                var url = '';
                for(var key in data){
                    url += '&'+key+'='+ encodeURIComponent(data[key]);
                }
                url = url.substring(1);
                (new Image()).src = '//stat.myzaker.com/stat_h5.php?'+url;
            },
            $video = $('#ID_video'),
            $popupDown = null,
            $btnPlay = null,
            popupHtml = '',
            popupCss = ".video_container{position:relative}.video_container .idvideo_play{position:absolute;left:0;top:0;z-index:2;width:100%;height:100%;overflow:hidden;background-color:#000;background-position:center;background-repeat:no-repeat;background-size:contain}.video_container .idvideo_play:before{content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.3) url(http://zkres.myzaker.com/static/zaker_ui/new/zk_mod/tpl/assets/css/img/play.png) no-repeat center;background-size:50px auto;-webkit-tap-highlight-color:transparent}.popup_down{position:fixed;left:0;top:0;z-index:99999;width:100%;height:100%;background:rgba(0,0,0,0.5);display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-pack:center;-webkit-justify-content:center;-ms-flex-pack:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center}.popup_down.hide{display:none}.popup_down .popcon{width:15.8rem;height:9.85rem;background-color:#fff;border-radius:.4rem;overflow:hidden}.popup_down .popcon .contxt{min-height:6.5rem}.popup_down .popcon p{height:1rem;line-height:1rem;font-size:.8rem;font-family:FZLTHK--GBK1-0,FZLTHK--GBK1;font-weight:normal;color:#3b3b3b}.popup_down .popcon p.txtcenter{padding-top:3rem;text-align:center}.popup_down .popcon .btns{position:relative;width:100%;height:4.4rem}.popup_down .popcon .btns:before{content:'';position:absolute;top:0;left:0;height:1px;width:100%;overflow:hidden;background-image:-webkit-linear-gradient(270deg, #ededed, #ededed 50%, transparent 50%);background-image:linear-gradient(180deg, #ededed, #ededed 50%, transparent 50%);background-size:100% 1px;background-position:top;background-repeat:no-repeat}.popup_down .popcon .btns .btn{float:left;display:block;width:50%;height:3.3rem;line-height:3.3rem;text-align:center;font-size:.8rem;color:#ababab}.popup_down .popcon .btns .btn.btnl{position:relative;font-family:FZLTHK--GBK1-0,FZLTHK--GBK1}.popup_down .popcon .btns .btn.btnl:after{box-sizing:border-box;line-height:0;background-color:#ededed;width:1px;height:100%;content:'';position:absolute;top:0;right:0;background-size:1px 1px;-webkit-transform:scaleX(.5);-webkit-transform-origin:0 0}.popup_down .popcon .btns .btn.btnr{font-weight:700;color:#00abff;font-family:FZLTZHK--GBK1-0,FZLTZHK--GBK1}";
            
        popupHtml = '<div class="popup_down hide" id="popup_down">\
            <div class="popcon">\
              <div class="contxt">\
                <p class="txtcenter">前往 ZAKER 视频体验更流畅</p>\
              </div>\
              <div class="btns">\
                <span class="btn btnl">稍后</span>\
                <span class="btn btnr">立即前往</span>\
              </div>\
            </div>\
          </div>';
        $('body').append(popupHtml).show();
        var videoPic = $video.attr('poster');
        $video.parents('.video_container').append('<div class="idvideo_play" style=" background-image: url('+videoPic+')"></div>');
        $video.css({opacity: 0});
        $('<style>').attr({'type': 'text/css'}).html(popupCss).appendTo($('head'));
        $popupDown = $('#popup_down');
        $btnPlay = $('.idvideo_play');
        $btnPlay.on('click', function(){
            $popupDown.removeClass('hide');
        });
        $('.btnl', $popupDown).on('click', function(){
            $btnPlay.remove();
            $popupDown.remove();
            $video.css({opacity: 1});
            $video[0].play();
            molg({id: 'Class_Pop_Later_Click'});
        });
        $('.btnr', $popupDown).on('click', function(){
            $btnPlay.remove();
            $popupDown.remove();
            $video.css({opacity: 1});
            molg({id: 'Class_Pop_Down_Click'});
            $('.zk_top_bar_link').click();
        });
    },
    zkTitBg: function () {
        var len = $('.zk_titbg').length;
        for (var i = 0; i < len; i++) {
            var $el = $('.zk_titbg').eq(i),
                val = $el.text(),
                _val = (val.split("").join("")).replace(/([\s\S])/g, "<span>$1</span>");
            $el.html(_val);
        }
    },
    //投票
    zkVote: {
        //talMain: TalMain,
        config: {
            voted: [],
            votes: {
                'sda': {}
            },
            colors: ['rgb(236, 144, 98)', 'rgb(236, 204, 99)', 'rgb(223, 115, 112)', 'rgb(253, 185, 51)', 'rgb(253, 91, 120)', 'rgb(42, 92, 170)', 'rgb(223, 112, 166)', 'rgb(255, 153, 255)', 'rgb(125, 173, 225)', 'rgb(222, 166, 129)', 'rgb(153, 102, 204)', 'rgb(76, 85, 110)', 'rgb(153, 153, 255)']
        },
        rand: function (n) {// n=2时，返回随机数：0、1
            return (Math.floor(Math.random() * n + 1) - 1);
        },
        init: function () {
            this.talMain = TalMain;
            var _ts = this,
                _c = _ts.config,
                $modVote = $('.zk_vote');
            $modVote.each(function (i, e) {
                var $el = $(e),
                    //voteId = $el.attr('data-voteid'),
                    zkInfo = JSON.parse(decodeURIComponent($el.attr('data-zkinfo') || '') || '{}');
                $el.attr('data-voteid', zkInfo.voteId);
                _ts.getData($el, { voteId: zkInfo.voteId });
            });
            _c.voted = $.parseJSON($.cookie('pollVoted') || '[]');
            //同步本地存储数据
            //_c.voted = $.parseJSON(window.localStorage.getItem('pollVoted') || '[]');
        },
        initHtml: function ($el, data) {
            var _ts = this,
                _c = _ts.config,
                arr = [],
                html = '';
            resultArr = [];

            arr.push('');
            arr.push('<div class="vote_tit">投票：' + data.title + '</div>');
            arr.push('<div class="vote_list" data-selectionid="' + data.selection_id + '">');
            for (var i = 0; i < data.options.length; i++) {
                var decs = data.options[i].subtitle != "" ? data.options[i].subtitle : data.options[i].title;
                arr.push('    <div class="vote_item" data-option_id="' + data.options[i].option_id + '">');
                arr.push('        <div class="vote_checkbox"></div>');
                arr.push('        <div class="vote_label">' + decs + '</div>');
                arr.push('    </div>');

                resultArr.push('    <div class="vote_item">');
                resultArr.push('        <div class="vote_label">' + decs + '</div>');
                resultArr.push('        <div class="vote_percent">' + data.options[i].rank + '%</div>');
                resultArr.push('        <div class="vote_bar"><div class="vote_barw" style="width: ' + data.options[i].rank + '%;"><div class="vote_barwc" style="background-color: ' + _c.colors[_ts.rand(_c.colors.length)] + ';"></div></div></div>');
                resultArr.push('    </div>');
            }
            arr.push('</div>');
            arr.push('<div class="vote_result">' + resultArr.join(''));

            /*arr.push('    <div class="vote_item">');
            arr.push('        <div class="vote_label">苹果：不正面回应+拖沓道歉+出新品</div>');
            arr.push('        <div class="vote_percent">20.3%</div>');
            arr.push('        <div class="vote_bar"><div class="vote_barw" style="width: 73px;"><div class="vote_barwc" style="background-color: rgb(236, 144, 98);"></div></div></div>');
            arr.push('    </div>');*/

            arr.push('</div>');
            arr.push('<div class="vote_btns">');
            arr.push('    <div class="vote_sub">投票</div>');
            arr.push('    <div class="vote_ck">查看</div>');
            arr.push('    <div class="vote_ret">返回</div>');
            arr.push('</div>');
            html = arr.join('');
            $el.html(html).show();
            //bind 绑定事件
            var $voteList = $('.vote_list', $el),
                $voteResult = $('.vote_result', $el),
                $voteSub = $('.vote_sub', $el),
                $voteCk = $('.vote_ck', $el),
                $voteRet = $('.vote_ret', $el);

            $voteSub.on('click', function () {

                //提交逻辑
                var voteId = $el.attr('data-voteid'),
                    options = [],
                    $votedItem = $('.vote_list .vote_item.cur', $el);

                for (var i = 0; i < $votedItem.length; i++) {
                    var option_id = $votedItem.attr('data-option_id');
                    options.push(option_id);
                }
                if (options.length <= 0) {
                    _ts.tips($el, { tips: '亲，你还没选投票项呢！' });
                    return false;
                }
                //var option_id = options.join(',');
                if (_c.voted.indexOf(voteId) < 0) {//未投票
                    _ts.sendData($el, { selection_id: data.selection_id, options: options, voteId: voteId });
                } else {//已经投票
                    _ts.tips($el, { tips: '亲，你已经投过票了哦！' });
                }
            });
            $voteCk.on('click', function () {
                $(this).hide();
                $voteSub.hide();
                $voteRet.show();
                $voteList.hide();
                $voteResult.show();
            });
            $voteRet.on('click', function () {
                $(this).hide();
                $voteSub.show();
                $voteCk.show();
                $voteList.show();
                $voteResult.hide();
            });
            $('.vote_item', $voteList).on('click', function () {
                if ($(this).hasClass('cur')) {
                    $(this).removeClass('cur');
                } else {
                    var $voteItemCur = $('.vote_item.cur', $voteList);
                    if ($voteItemCur.length >= data.mutiple) {
                        $voteItemCur.eq(0).removeClass('cur');
                    }
                    $(this).addClass('cur');
                }
            });
        },
        tips: function ($el, data) {
            if ($('.tips', $el).length <= 0) {
                $el.append('<span class="tips">' + data.tips + '</span>');
            }
            /*$('.tips', $el).fadeOut(3000, function(){
                $('.tips', $el).remove();
            });*/
            setTimeout(function () {
                $('.tips', $el).remove();
            }, 1500);
        },
        getData: function ($el, data) {
            var _ts = this,
                _c = _ts.config;
            $.ajax({
                //url: 'http://iphone.myzaker.com/zaker/topic_vote_data.php',
                url: 'https://iphone.myzaker.com/zaker/topic_vote_data.php',
                type: 'get',
                dataType: 'jsonp',
                data: {
                    id: data.voteId
                },
                success: function (res) {
                    if (res.stat == '1' && typeof res.data == 'object') {
                        var voteData = res.data.vote;
                        //测试
                        //voteData.mutiple = '2';
                        _c.votes[voteData['selection_id']] = voteData;
                        _ts.initHtml($el, voteData);
                    }
                },
                error: function () {

                }
            });
        },
        sendData: function ($el, data) {
            var _ts = this,
                _c = _ts.config,
                pageData = _ts.talMain.config.pageData || {},
                selection_id = data.selection_id,
                options = data.options,
                params = _ts.talMain.tools.assign(pageData, {
                    data: [{
                        selection_id: selection_id,
                        option_id: options.join(',')
                    }]
                }),
                votedCur = _c.votes[selection_id];
            $.ajax({
                url: votedCur.action_vote_url, //'http://121.9.213.58/huodong.myzaker.com/main/interactive/vote/index.php?hid=5c46e8ea5af0d6c92f000005&action=save2',
                type: 'post',
                dataType: 'json',
                data: params,
                success: function (res) {
                    if (res.stat == '1' && typeof res.data == 'object') {
                        votedCur.total = parseInt(votedCur.total, 10) + options.length;
                        var ranks = 0,
                            resultArr = [];

                        for (var i = 0; i < votedCur.options.length; i++) {
                            var rank = 0;
                            votedCur.options[i].total = parseInt(votedCur.options[i].total, 10);
                            if (options.indexOf(votedCur.options[i].option_id) >= 0) {//当前选中的item
                                votedCur.options[i].total = votedCur.options[i].total + 1;
                            }
                            if (i >= votedCur['options'].length - 1) {//最后
                                rank = 100 - ranks;
                            } else {
                                rank = parseInt((votedCur.options[i].total / votedCur.total) * 100, 10);
                                ranks = ranks + rank;
                            }
                            votedCur['options'][i].rank = rank;
                            var decs = (votedCur.options[i].subtitle != "") ? votedCur.options[i].subtitle : votedCur.options[i].title;
                            resultArr.push('    <div class="vote_item">');
                            resultArr.push('        <div class="vote_label">' + decs + '</div>');
                            resultArr.push('        <div class="vote_percent">' + votedCur.options[i].rank + '%</div>');
                            resultArr.push('        <div class="vote_bar"><div class="vote_barw" style="width: ' + votedCur.options[i].rank + '%;"><div class="vote_barwc" style="background-color: ' + _c.colors[_ts.rand(_c.colors.length)] + ';"></div></div></div>');
                            resultArr.push('    </div>');
                        }
                        $('.vote_result', $el).html(resultArr.join('')).show();
                        $('.vote_list', $el).hide();

                        $('.vote_sub', $el).hide();
                        $('.vote_ck', $el).hide();
                        $('.vote_ret', $el).show();
                        _ts.tips($el, { tips: '感谢你宝贵的投票！' });
                        _ts.saveData(data.voteId);
                    } else {
                        _ts.tips($el, { tips: res.msg });
                    }
                },
                error: function () {
                    _ts.tips($el, { tips: '网络错误' });
                }
            });
        },
        saveData: function (voteId) {
            var _ts = this,
                _c = _ts.config,
                //pollVoted = window.localStorage.getItem('pollVoted') || 0,
                pollVoted = $.cookie('pollVoted') || 0,
                data = $.parseJSON(pollVoted) || [],
                dataLen = data.length || 0,
                isExist = false;//默认当前记录还没有保存
            if (data.indexOf(voteId) < 0) { //不存在 //$.inArray()
                data.unshift(voteId);
                if (dataLen > 50) { data.pop(); }
            }
            //cooke存储
            $.cookie('pollVoted', JSON.stringify(data), { expires: 7 });
            _c.voted = data;
            /*//本地存储数据
            try{
                window.localStorage.setItem("pollVoted", JSON.stringify(data));
                _c.voted = data;
            }catch(oException){
                if(oException.name == 'QuotaExceededError'){
                    var _data = [];
                        _data.push(voteId);
                    //localStorage.clear();
                    window.localStorage.removeItem('pollVoted');
                    window.localStorage.setItem("pollVoted", JSON.stringify(_data));
                }
            }*/
        }
    },
    //mp3播放器
    zkAudio: {
        config: {
            timer: null
        },
        init: function () {
            var _ts = this,
                _c = _ts.config,
                $modMusic = $('.zk_audio'),
                allFun = function ($modMusic, tsi) {
                    var musicLen = $modMusic.size(),
                        $tsMusicCon = null,
                        $tsAudioMp3 = null,
                        tsOAudioMp3 = null;

                    clearInterval(_c.timer);
                    for (var i = 0; i < musicLen; i++) {
                        if (i === tsi) { continue; }
                        $tsMusicCon = $modMusic.eq(i);
                        $tsAudioMp3 = $tsMusicCon.find('.audio_mp3');
                        tsOAudioMp3 = $tsAudioMp3[0];
                        tsOAudioMp3.pause();
                        $tsMusicCon.removeClass('open');
                    }
                };

            var ua = window.navigator.userAgent.toLowerCase();
            if (ua.match(/zaker/ig) && ua.match(/android/ig)) {//设置app不左右滑动
                var winW = document.documentElement.clientWidth;
                alert('zkCanScrollInfo:{"action_type":"add", "isDisableScrollVertically": true, "isDisableScrollHorizontally": false, "x":0,"y":0,"width":' + winW + ',"height":10000}');
            }
            $modMusic.each(function (i, e) {
                var $ts = $(e);
                //渲染数据
                var zkInfoData = JSON.parse(decodeURIComponent($ts.attr('data-zkinfo') || '') || '{}');
                if (zkInfoData.url) {
                    _ts.initHtml($ts, zkInfoData);
                }
                var curSrc = '',
                    $tslink = $(".audio_play", $ts),
                    $progress = $(".progress", $ts),
                    $tsAudioMp3 = $('.audio_mp3', $ts),
                    $tsModMusicCon = $ts, //$('.music_con', $ts),
                    tsOAudioMp3 = $tsAudioMp3[0];

                /*var ua = window.navigator.userAgent.toLowerCase();
                if(ua.match(/zaker/ig) && ua.match(/android/ig)){//设置app不左右滑动
                    var _xPro = parseInt($tsModMusicCon.offset().left, 10),
                        _yPro = parseInt($tsModMusicCon.offset().top, 10),
                        _wPro = parseInt($tsModMusicCon.width(), 10)+40,
                        _hPro = parseInt($tsModMusicCon.height(), 10)+40;
                    alert('zkCanScrollInfo:{"action_type":"add", "isDisableScrollVertically": "true", "isDisableScrollHorizontally": "false", "x":'+_xPro+',"y":'+_yPro+',"width":'+_wPro+',"height":'+_hPro+'}');
                }*/

                tsOAudioMp3.addEventListener('canplaythrough', function (e) {
                    $('.audio_times', $ts).html(_ts.changeTime(tsOAudioMp3.duration));
                }, false);
                tsOAudioMp3.load();
                //设置总时长 
                $tslink.on("click", function () {

                    if ($tsAudioMp3.data('done') === false) {
                        curSrc = $tsAudioMp3.data('osrc');
                        $tsAudioMp3.attr('src', curSrc);
                        $tsAudioMp3.find('source').attr('src', curSrc);
                        $tsAudioMp3.data('done', true);
                    }

                    allFun($modMusic, i);
                    if (tsOAudioMp3.paused) {//暂停状态
                        tsOAudioMp3.play();
                        $tsModMusicCon.addClass('open');
                        //$tsModMusicCon.addClass('openDone');
                        //this.value = '暂停';
                        _ts.nowTime($tsModMusicCon, tsOAudioMp3);
                        _c.timer = setInterval(function () {
                            _ts.nowTime($tsModMusicCon, tsOAudioMp3);
                        }, 1000);
                    } else {
                        tsOAudioMp3.pause();
                        $tsModMusicCon.removeClass('open');
                        //this.value = '播放';
                        clearInterval(_c.timer);
                    }
                });

                //播放结束
                tsOAudioMp3.addEventListener("ended", function () {
                    //$tsModMusicCon.find('.link .time').text('加载中...');
                    $tsModMusicCon.removeClass('open');
                    //$tsModMusicCon.removeClass('openDone');
                    $progress.find('.bar').css('width', '0%');
                    $tsModMusicCon.find('.audio_time').html('00:00');
                    return false;
                }, false);

                //触摸播放器进度条
                _ts.barTouch($tsModMusicCon, tsOAudioMp3);
            });
            //提给安卓客户端调用关闭音频
            window.ZKAudio = function (type) {
                if (type == 'close') {
                    var $modMusic = $('.zk_audio');
                    for (var i = 0; i < $modMusic.length; i++) {
                        var $ts = $modMusic[i],
                            tsOAudioMp3 = $('.audio_mp3', $ts)[0];
                        $($ts).removeClass('open');
                        tsOAudioMp3.pause();
                        clearInterval(_c.timer);
                    };
                }
            };

        },
        initHtml: function ($el, data) {
            var arr = [];
            arr.push('');
            arr.push('<div class="audio_con">');
            arr.push('    <div class="audio_play"></div>');
            arr.push('    <div class="audio_info">');
            arr.push('        <div class="audio_title">' + data.title + '</div>');
            arr.push('        <div class="audio_decs">' + data.author + '</div>');
            arr.push('    </div>');
            arr.push('</div>');
            arr.push('<div class="audio_foot">');
            arr.push('    <div class="audio_time">00:00</div>');
            arr.push('    <div class="audio_times">' + data.time + '</div>');
            arr.push('    <div class="progress">');
            arr.push('        <div class="bar_bg">');
            arr.push('            <div class="bar" style="width: 0%;"></div>');
            arr.push('        </div>');
            arr.push('    </div>');
            arr.push('</div>');
            arr.push('<div class="audio_mp3con">');
            arr.push('    <audio class="audio_mp3" preload="auto" src="' + data.url + '"></audio>');
            arr.push('</div>');
            $el.html(arr.join(''));
        },
        nowTime: function ($modMusicCon, oAudioMp3, scale) {
            var _ts = this,
                _c = _ts.config;
            //scale = (oAudioMp3.currentTime/oAudioMp3.duration)*100;
            if (typeof scale == 'undefined') {
                scale = oAudioMp3.currentTime / oAudioMp3.duration;
            }
            var currentTime = scale * oAudioMp3.duration;
            if (currentTime >= oAudioMp3.duration) {
                clearInterval(_c.timer);
                return false;
            }
            if (currentTime > 0) {
                $modMusicCon.find('.audio_time').html(_ts.changeTime(currentTime));
            }
            $modMusicCon.find('.progress .bar').css('width', scale * 100 + '%');
        },
        changeTime: function (iNumc) {
            var _ts = this,
                iNum = parseInt(iNumc, 10);
            /*var iH = _ts.toZero(Math.floor(iNum/3600));*/
            var iM = _ts.toZero(Math.floor(iNum % 3600 / 60));
            var iS = _ts.toZero(Math.floor(iNum % 60));
            return iM + ':' + iS;
        },
        toZero: function (num) {
            if (num <= 9) {
                return '0' + num;
            } else {
                return '' + num;
            }
        },
        barTouch: function ($modMusicCon, oAudioMp3) {
            var _ts = this,
                $progress = $modMusicCon.find('.progress'),
                $bar = $progress.find('.bar');

            touch.on($progress, 'touchstart', function (e) {
                // client / clientY：触摸点相对于浏览器窗口viewport的位置
                // pageX / pageY：触摸点相对于页面的位置
                // screenX /screenY：触摸点相对于屏幕的位置
                // identifier： touch对象的unique ID
                var touch = e.touches[0],
                    scale = 0,
                    L = (touch.clientX - $progress.offset().left);

                //oAudioMp3.pause();
                if (L < 0) {
                    L = 0;
                } else if (L > $progress.width()) {
                    L = $progress.width();
                }
                scale = L / $progress.width();
                $bar.css('width', scale * 100 + '%');
                $progress.data('startx', touch.clientX).data('startw', $bar.width());

                //oAudioMp3.currentTime = scale * oAudioMp3.duration;
                _ts.nowTime($modMusicCon, oAudioMp3, scale);
            });
            touch.on($progress, 'touchmove touchend', function (e) {
                var ts = $(this);
                setTimeout(function () {
                    var touch = e.touches[0],
                        startx = $progress.data('startx'),
                        startw = $progress.data('startw'),
                        scale = 0,
                        L = (touch.clientX - startx) + startw;
                    if (L < 0) {
                        L = 0;
                    } else if (L > $progress.width()) {
                        L = $progress.width();
                    }

                    scale = L / $progress.width();
                    $bar.css('width', scale * 100 + '%');
                    //oAudioMp3.currentTime = scale * oAudioMp3.duration;
                    _ts.nowTime($modMusicCon, oAudioMp3, scale);
                }, 80);
            });
            touch.on($progress, 'touchend', function (e) {
                setTimeout(function () {
                    var scale = $bar.width() / $progress.width();
                    oAudioMp3.currentTime = scale * oAudioMp3.duration;
                    //oAudioMp3.play();
                }, 80);
            });
            //pc端绑定点击进度条
            if (!navigator.userAgent.match(/iphone|ipod|Android/ig)) {
                $progress.on('click', function (e) {
                    var w = $progress.width();
                    var barW = e.clientX - $progress.offset().left;
                    var scale = barW / w;
                    _ts.nowTime($modMusicCon, oAudioMp3, scale);
                    oAudioMp3.currentTime = scale * oAudioMp3.duration;
                });
            }
        }
    },
    // scroll img
    zkScrollImg: {
        config: {
            oScroll: {}
        },
        init: function () {
            //return false;
            var _ts = this,
                $mod = $('.zk_scrollimg');
            if (navigator.userAgent.match(/iphone|ipod|Android/ig)) {
                //console.log('$mod', $mod.css('margin-left'));
                $('.zk_scrollimg')[0].offsetLeft
                var ol = $('.zk_scrollimg')[0].offsetLeft || 20,
                    winW = document.documentElement.clientWidth || document.body.clientWidth;
                $mod.css({
                    'margin-left': '-' + ol + 'px',
                    'width': winW + 'px'
                })
            }
            function handler() {
                event.preventDefault();
            }

            for (var i = 0; i < $mod.length; i++) {
                var $el = $mod.eq(i),
                    zkInfoData = JSON.parse(decodeURIComponent($el.attr('data-zkinfo') || '') || '{}');
                _ts.initHtml($el, zkInfoData);

                new iScroll($el[0], {
                    hScroll: true,
                    vScroll: true,
                    hScrollbar: false,
                    vScrollbar: false,
                    onScrollEnd: function () {
                        // 太右
                        /*if(Math.abs(parseInt($ul.css('left'), 10)) > $ul.width()){
                            $ul.css('left',$ul.width()-$('body')[0].clientWidth + 'px');
                        }else if($ul.css('left') > 0){
                            // 太左
                            $ul.css('left', '0px');
                        }
                        setTimeout(function(){
                            ulstyle= $('ul', $mainNav).attr('style');
                            styleData= {
                                '#top_mainNav ul': '{'+ulstyle+'}'
                            };
                            apm.tools.cookiesSetOneDay('navRecordStyle', JSON.stringify(styleData));
                            //cookies.set('navRecordStyle', JSON.stringify(styleData));
                        }, 100);*/
                    },
                    onBeforeScrollStart: function (e) {
                        $(e.target).parents('.zk_scrollimg')[0].addEventListener('touchmove', handler, false);
                    },
                    onBeforeScrollMove: function (e) {
                        //alert(7);
                        //$(e.target).parents('.zk_scrollimg')[0].removeEventListener('touchmove', handler, false);
                        var y = this.absDistY,
                            x = this.absDistX,
                            _ts = this;
                        setTimeout(function () {
                            if (Math.abs(x - _ts.absDistX) < 8 && Math.abs(y - _ts.absDistY) > 20) {//上下滑动
                                $(e.target).parents('.zk_scrollimg')[0].removeEventListener('touchmove', handler, false);
                            }
                        }, 100)
                    },
                    onScrollMove: function () {
                        //alert(111);
                        console.log(8);
                    },
                    //解决第一次无法滑动的问题
                    onTouchEnd: function () {
                        var self = this;
                        if (self.touchEndTimeId) {
                            clearTimeout(self.touchEndTimeId);
                        }
                        self.touchEndTimeId = setTimeout(function () {
                            self.absDistX = 0;
                            self.absDistY = 0;
                        }, 600);
                    }
                });
            }

        },
        initHtml: function ($el, data) {
            var arr = []
            items = data.items;
            /*for(var i=0; i<items.length; i++){
                arr.push('    <div class="item" style="width: '+items[i].width+'px; height: '+items[i].height+'px;" data-width="'+items[i].width+'" data-height="'+items[i].height+'"><img src="'+items[i].url+'" /></div>');
            }
            arr.push('</div>');
            $el.html(arr.join(''));*/

            var mr = 5,
                h = data.modH,
                w = 0;
            for (var i = 0; i < items.length; i++) {
                var _w = parseInt(items[i].width, 10),
                    _h = parseInt(items[i].height, 10),
                    wCur = parseInt(((_w / _h) * h), 10);
                arr.push('<div class="item" style="width: ' + wCur + 'px;" data-width="' + items[i].width + '" data-height="' + items[i].height + '">');
                arr.push('    <img src="' + items[i].url + '" />');
                arr.push('</div>');
                w += wCur + mr;
            }
            w = w - mr;
            $el.html('<div class="items" style="width: ' + w + 'px;" >' + arr.join('') + '</div>');

            //$items.css('width', w+'px');
        }
    },
    zkUpVideo: {
        config: {
            timer: null,
            control_timer: null,
            videoVer: 8.67,
        },
        init: function () {
            var _ts = this,
                $mod = $('.zk_upvideo');

            //winW = document.documentElement.clientWidth || document.body.clientWidth;
            /*$mod.css({
                height: winW*0.5+'px'
            });*/
            var isIos12 = true;//TalMain.tools.getIosVer() != null && Number(TalMain.tools.getIosVer().split('.')[0]) >= 12 && TalMain.tools.appType() == 'zaker';
            /*//测试
            isIos12 = true;*/
            // $('.temp_header_author').text(isIos12 + ' '+TalMain.tools.getIosVer() + ' ' + Number(TalMain.tools.getIosVer().split('.')[0]));
            for (var i = 0; i < $mod.length; i++) {
                (function (i) {
                    var $el = $mod.eq(i);
                    var zkInfoData = JSON.parse(decodeURIComponent($el.attr('data-zkinfo') || '') || '{}');
                    _ts.initHtml($el, zkInfoData, isIos12);
                    if (isIos12) {
                        var curSrc = '',
                            $tslink = $(".zk_videoplayer_play", $el),
                            $progress = $(".zk_videoplayer_playprogress_per", $el),
                            $video = $('video', $el),
                            tsOVideo = $video[0],
                            timeTotal = 0;

                        $video.index = i;
                        //$video[0].controlsList.add('nodownload');

                        // $('.zk_videoplayer_loading', $el).hide();
                        $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                        
                        var ua = window.navigator.userAgent.toLowerCase();
                        if (TalMain.tools.platform == 'ios') {
                            // ios监听不到 loadedmetadata canplay canplaythrough等事件，所以不做初始化loading效果
                            $('.zk_videoplayer_loading', $el).hide();
                            $('.zk_videoplayer_controlwrapper', $el).css('opacity', '1');
                            timeTotal = _ts.changeTime(tsOVideo.duration);
                            $('.zk_videoplayer_time.total', $el).html(_ts.changeTime(tsOVideo.duration));
                        }

                        $video.on('loadedmetadata canplay', function () {
                            // 移除loading效果，设置总时间
                            $('.zk_videoplayer_loading', $el).hide();
                            $('.zk_videoplayer_controlwrapper', $el).css('opacity', '1');
                            timeTotal = _ts.changeTime(tsOVideo.duration);
                            $('.zk_videoplayer_time.total', $el).html(_ts.changeTime(tsOVideo.duration));
                        });

                        // 监听是否全屏
                        if (TalMain.tools.platform == 'ios') {
                            $video.on('webkitendfullscreen', function () {
                                $el.find('.zk_videoplayer_controlbar').css({
                                    'width': '100%',
                                    'left': '0',
                                    'margin-left': '0px'
                                });
                                if (tsOVideo.currentTime != tsOVideo.duration) {
                                    $('.zk_videoplayer_share', $el).removeClass('show') 
                                }
                                // setTimeout(() => {
                                //     tsOVideo.play()
                                //     if (tsOVideo.paused) {
                                //         $el.find('.zk_videoplayer_play').addClass('play')  
                                //     } else {
                                //         $el.find('.zk_videoplayer_play').removeClass('play')  
                                //     }    
                                // }, 500);
                            });
                        } else {
                            var prefixes = ["", "moz", "webkit", "ms"]
                            prefixes.forEach(function (prefix) {
                                $el.on(prefix + "fullscreenchange", function () {
                                    var fullscreenelement = document.fullscreenElement || /* Standard syntax */
                                        document.webkitFullscreenElement || /* Chrome, Safari and Opera syntax */
                                        document.mozFullScreenElement ||/* Firefox syntax */
                                        document.msFullscreenElement /* IE/Edge syntax */;
                                    if (fullscreenelement) {
                                        $el.addClass('fullscreen')
                                        isFullScreen = true
                                    } else if (!document.fullscreenchange) {
                                        $el.find('.zk_videoplayer_controlbar').css({
                                            'width': '100%',
                                            'left': '0',
                                            'margin-left': '0px'
                                        });
                                        $el.removeClass('fullscreen')
                                        isFullScreen = false
                                    }            
                                });
                            });
                        }


                        // 播放时缓冲数据
                        $video.on('waiting', function () {
                            $('.zk_videoplayer_loading', $el).show();
                            $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                        })

                        $video.on('play', function () {
                            //暂停其它的视频
                            _ts.allClose($mod, this);
                            // $('.zk_videoplayer_loading', $el).hide();
                            // clearTimeout(_ts.config.control_timer)
                            // //$('.zk_videoplayer_controlbar', $el).css('opacity', '1');
                            // _ts.config.control_timer = setTimeout(function(){
                            //     $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                            // }, 3000)
                        })

                        $video.on('timeupdate', function () {
                            //播放时还原真实高宽比
                            var width = this.videoWidth;
                            var height = this.videoHeight;
                            var per = height / width * 100 + '%'
                            if (per) {
                                $(this).parents('.zk_upvideo').css('padding-bottom', per)
                            }                     
                        })

                        $video.on('playing', function (e) {
                            $('.zk_videoplayer_loading', $el).hide();
                            $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                            // $('.zk_videoplayer_share', $el).removeClass('show');
                            if (!timeTotal) {
                                $('.zk_videoplayer_time.total', $el).html(_ts.changeTime(tsOVideo.duration));
                            }
                        })

                        // 点击播放、暂停
                        $tslink.on("click", function () {
                            // if($video.data('done') === false){
                            //     curSrc = $video.data('osrc');
                            //     $video.attr('src', curSrc);
                            //     $video.find('source').attr('src', curSrc);
                            //     $video.data('done', true);
                            // }
                            $el.find('.zk_videoplayer_play').removeClass("init")
                            $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                            if ($(".zk_videoplayer_play_opa", $el).length) {
                                $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                            } else {
                                $('.zk_videoplayer_controlwrapper', $el).css('opacity', '1');
                                $('.zk_videoplayer_controlbar', $el).css('opacity', '1');
                            }
                            clearTimeout(_ts.config.control_timer);
                            allFun($mod, i);
                            if (tsOVideo.paused) {//暂停状态
                                tsOVideo.play();
                                $el.find('.zk_videoplayer_play').addClass('play');
                                //$el.addClass('openDone');
                                //this.value = '暂停';
                                _ts.nowTime($el, tsOVideo);
                                _c.timer = setInterval(function () {
                                    _ts.nowTime($el, tsOVideo);
                                }, 1000);
                                _ts.config.control_timer = setTimeout(function () {
                                    $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                                }, 3000)
                            } else {
                                tsOVideo.pause();
                                $el.find('.zk_videoplayer_play').removeClass('play');
                                //this.value = '播放';
                                clearInterval(_c.timer);
                            }
                            if ($(".zk_videoplayer_play_opa", $el).length) { $(".zk_videoplayer_play_opa", $el).remove(); }
                        });

                        $(".zk_videoplayer_play_opa", $el).on('click', function () {
                            $tslink.css('opacity', '1');
                            //$(this).remove();
                            $tslink.click();
                        });

                        //播放暂停
                        tsOVideo.addEventListener("pause", function () {
                            $el.find('.zk_videoplayer_play').removeClass('play');
                            $el.find('.zk_videoplayer_controlwrapper').css('opacity', '1');
                        }, false);

                        //播放结束
                        tsOVideo.addEventListener("ended", function () {
                            $el.find('.zk_videoplayer_share').addClass('show');
                            $el.find('.zk_videoplayer_play').removeClass('play');
                            $el.find('.zk_videoplayer_controlwrapper').css('opacity', '0');
                            $el.find('.zk_videoplayer_playprogress_per').css({ 'width': '0%' });
                            $el.find('.zk_videoplayer_time').eq(0).html('00:00');
                        }, false);

                        //触摸播放器进度条
                        _ts.barTouch($el, tsOVideo);
                      
                        // 全屏
                        var isFullScreen = false
                        $el.find('.zk_videoplayer_fullscreen').on('click', function () {
                            if (isFullScreen) {
                                _ts.exitFullscreen()
                            } else {
                                var notCanFullscreen = false
                                try {
                                    var videoWidth = tsOVideo.videoWidth
                                    var videoHeight = tsOVideo.videoHeight
                                    if (videoWidth > videoHeight) {
                                        $el.addClass('horizontal')
                                        var _height = Math.min(window.screen.width, window.screen.height)
                                        var fullscreenWidth = (videoWidth / videoHeight) * _height
                                        $el.find('.zk_videoplayer_controlbar').css({
                                            'width': fullscreenWidth + 'px',
                                            'left': '50%',
                                            'margin-left': '-'+fullscreenWidth/2+'px'
                                        });
                                    }
                                    var fullscreenDom = $el[0]
                                    if (TalMain.tools.platform == 'ios' || TalMain.tools.getAndroidVer() <= 5) {
                                        // ios只能全屏video标签
                                        fullscreenDom = $el.find('video')[0]
                                    }
                                    _ts.launchIntoFullscreen(fullscreenDom)                                   
                                } catch(err) {
                                    notCanFullscreen = true
                                    console.log(err)
                                }
                                if(!notCanFullscreen) {
                                    if (TalMain.tools.appType() == 'zaker' && TalMain.tools.getUrlName('_version') >= _ts.config.videoVer) {
                                        _ts.zkvideo_info(videoWidth, videoHeight)
                                    }
                                }
                                // allFun($mod, i);
                            }
                        })

                        // 点击视频显示播放面板
                        $('.zk_videoplayer_controlwrapper', $el).on('click', function () {
                            clearTimeout(_ts.config.control_timer)
                            var $control = $('.zk_videoplayer_controlwrapper', $el), _opac;
                            $('.zk_videoplayer_controlbar', $el).css('opacity', '1');
                            if (tsOVideo.paused && $control.css('opacity') == 1) return
                            if ($control.css('opacity') == 0) {
                                _opac = '1'
                                _ts.config.control_timer = setTimeout(function () {
                                    $('.zk_videoplayer_controlwrapper', $el).css('opacity', '0');
                                }, 3000)
                            } else {
                                _opac = '0'
                            }
                            $control.css('opacity', _opac);
                        })

                        // 静音
                        $('.zk_videoplayer_muted', $el).on('click', function () {
                            tsOVideo.muted = !tsOVideo.muted
                            if (tsOVideo.muted) {
                                $(this).addClass('muted')
                            } else {
                                $(this).removeClass('muted')
                            }
                        })

                        // 重播
                        $('.zk_videoplayer_btn_replay', $el).on('click', function () {
                            $('.zk_videoplayer_share', $el).removeClass('show')
                            $tslink.click()
                            window.statLink && _ts.stat(window.statLink.replayVideo)  
                        })

                        // 分享
                        if (window.shareInfo) {
                            $('.zk_videoplayer_btn_wx').on('click', function () {
                                window.statLink && _ts.stat(window.statLink.shareWechat)
                                // if (window.navigator.userAgent.toLowerCase().match(/android/ig)) {
                                //     alert(window.shareInfo.link)
                                //     return
                                // }
                                window.location.href = window.shareInfo.link     
                            })
                            $('.zk_videoplayer_btn_pyq').on('click', function () {
                                window.statLink && _ts.stat(window.statLink.shareTimeline)
                                // if (window.navigator.userAgent.toLowerCase().match(/android/ig)) {
                                //     alert(window.shareInfo.timeline)
                                //     return
                                // }
                                window.location.href = window.shareInfo.timeline
                            })
                        }
                    }
                })(i)
            }
            /*(function(window){
                var videos = document.getElementsByTagName('video');
                HTMLCollection.prototype.toArray=function(){
                    console.log('we');
                    return [].slice.call(this);
                };
                videos.toArray().forEach(function(item){
                    //console.log('item', item);
                    //item.controlsList.add('nodownload');
                    console.log(item.controlsList);
                });
            })(window)*/
            _c = _ts.config,

                allFun = function ($modMusic, tsi) {
                    var videoLen = $modMusic.size(),
                        $tsVideoCon = null,
                        $tsVideo = null,
                        tsOVideo = null;

                    clearInterval(_c.timer);
                    for (var i = 0; i < videoLen; i++) {
                        if (i === tsi) { continue; }
                        $tsVideoCon = $modMusic.eq(i);
                        $tsVideo = $tsVideoCon.find('video');
                        tsOVideo = $tsVideo[0];
                        tsOVideo.pause();
                        $tsVideoCon.find('.zk_videoplayer_play').removeClass('play');
                    }
                };

            var ua = window.navigator.userAgent.toLowerCase();
            if (ua.match(/zaker/ig) && ua.match(/android/ig)) {//设置app不左右滑动
                var winW = document.documentElement.clientWidth;
                alert('zkCanScrollInfo:{"action_type":"add", "isDisableScrollVertically": true, "isDisableScrollHorizontally": false, "x":0,"y":0,"width":' + winW + ',"height":10000}');
            }
        },
        initHtml: function ($el, data, isInit) {
            var _ts = this;
            var arr = [];
            //preload="auto"
            if (isInit) {
                var perBlockWidth = $el.width() * (44 / 736)
                // 端内ios12及以上版本用自己写播放器组件 
                var videoHtml = '<div class="zk_videoplayer">';
                videoHtml += '<div class="zk_videoplayer_loading"></div>';
                videoHtml += '<video src="' + data.videoUrl + '" preload controlsList="nodownload noremoteplayback" onContextMenu="return false" poster="' + data.imgUrl + '" x5-video-player-fullscreen="true" playsinline="true" webkit-playsinline="true" x-webkit-airplay="true"> </video>';
                videoHtml += '<div class="zk_videoplayer_controlwrapper">';
                videoHtml += '<span class="zk_videoplayer_play_opa"></span>';
                videoHtml += '<span class="zk_videoplayer_play init"></span>';
                videoHtml += '<div class="zk_videoplayer_controlbar">';
                videoHtml += '<div class="zk_videoplayer_controlbar_inner">';
                videoHtml += '<a href="javascript:;" class="zk_videoplayer_muted"></a>';
                videoHtml += '<div class="zk_videoplayer_time">00:00</div>';
                videoHtml += '<div class="zk_videoplayer_playprogress"><div class="zk_videoplayer_playprogress_bg">';
                videoHtml += '<div class="zk_videoplayer_playprogress_per_wrap">';
                videoHtml += '<div class="zk_videoplayer_playprogress_per"><div class="zk_videoplayer_playprogress_per_block"></div></div></div></div>';
                videoHtml += '</div>';
                videoHtml += '<div class="zk_videoplayer_time total"></div>';
                videoHtml += '<a href="javascript:;" class="zk_videoplayer_fullscreen"></a>';
                videoHtml += '</div></div></div>';
                videoHtml += '<div class="zk_videoplayer_share">';
                videoHtml += '<a href="javascript:;" class="zk_videoplayer_btn zk_videoplayer_btn_replay"></a>';
                // if (window.shareInfo && window.shareInfo.link) {
                //     videoHtml += '<a href="javascript:;" class="zk_videoplayer_btn zk_videoplayer_btn_wx"></a>';
                // }
                // if (window.shareInfo && window.shareInfo.timeline) {
                //     videoHtml += '<a href="javascript:;" class="zk_videoplayer_btn zk_videoplayer_btn_pyq"></a>';
                // }
                videoHtml += '</div></div>';
            } else {
                //var videoHtml = '<video src="'+data.videoUrl+'" controlsList="nodownload nofullscreen noremoteplayback" onContextMenu="return false" poster="'+data.imgUrl+'" x5-video-player-fullscreen="true" playsinline="true" webkit-playsinline="true" x-webkit-airplay="true" controls="controls"> </video>';
                var videoHtml = '<video src="' + data.videoUrl + '" controlsList="nodownload noremoteplayback" onContextMenu="return false" controls="controls" poster="' + data.imgUrl + '" webkit-playsinline="webkit-playsinline" playsinline="playsinline" x5-playsinline="x5-playsinline" x-webkit-airplay="allow"> </video>';
            }
            arr.push(videoHtml);
            $el.html(arr.join(''));

            // 老版本安卓端不支持全屏事件，隐藏全屏按钮
            if (TalMain.tools.appType() == 'zaker' &&
                window.navigator.userAgent.toLowerCase().match(/android/ig) &&
                TalMain.tools.getUrlName('_version') < _ts.config.videoVer
                ) {
                $('.zk_videoplayer_fullscreen').remove();
            }
        },
        allClose: function ($mod, ts) {
            var len = $mod.length,
                indexCur = $mod.index($(ts).parents('.zk_upvideo'));
            for (var i = 0; i < len; i++) {
                if (indexCur == i) { continue; }//break
                var $el = $mod.eq(i),
                    $video = $('video', $el);
                $video[0].pause();
            }
        },
        nowTime: function ($modMusicCon, oVideo, scale) {
            var _ts = this,
                _c = _ts.config;
            //scale = (oVideo.currentTime/oVideo.duration)*100;
            if (typeof scale == 'undefined') {
                scale = oVideo.currentTime / oVideo.duration;
            }
            var currentTime = scale * oVideo.duration;
            if (currentTime >= oVideo.duration) {
                clearInterval(_c.timer);
                // $modMusicCon.find('.zk_videoplayer_time').eq(0).html(_ts.changeTime(oVideo.duration));
                // $modMusicCon.find('.zk_videoplayer_playprogress_per').css('width', 100 +'%');
                return false;
            }
            if (currentTime > 0) {
                $modMusicCon.find('.zk_videoplayer_time').eq(0).html(_ts.changeTime(currentTime));
            }

            $modMusicCon.find('.zk_videoplayer_playprogress_per').css('width', scale * 100 + '%');
        },
        changeTime: function (iNumc) {
            var _ts = this,
                iNum = parseInt(Math.round(iNumc), 10);
            /*var iH = _ts.toZero(Math.floor(iNum/3600));*/
            var iM = _ts.toZero(Math.floor(iNum % 3600 / 60));
            var iS = _ts.toZero(Math.floor(iNum % 60));
            if(isNaN(iM) || isNaN(iS)) return ''
            return iM + ':' + iS;
        },
        toZero: function (num) {
            if (num <= 9) {
                return '0' + num;
            } else {
                return '' + num;
            }
        },
        barTouch: function ($modVideoCon, oVideo) {
            var _ts = this,
                $progress = $modVideoCon.find('.zk_videoplayer_playprogress_per_wrap'),
                $bar = $progress.find('.zk_videoplayer_playprogress_per'),
                _c = _ts.config;

            //pc端绑定点击进度条
            if (!navigator.userAgent.match(/iphone|ipod|Android/ig)) {
                $progress.on('click', function (e) {
                    var w = $progress.width();
                    var barW = e.clientX - $progress.offset().left;
                    var scale = barW / w;
                    _ts.nowTime($modVideoCon, oVideo, scale);
                    oVideo.currentTime = scale * oVideo.duration;
                    e.stopPropagation()
                });
            } else {
                touch.on($progress, 'touchstart', function (e) {
                    // client / clientY：触摸点相对于浏览器窗口viewport的位置
                    // pageX / pageY：触摸点相对于页面的位置
                    // screenX /screenY：触摸点相对于屏幕的位置
                    // identifier： touch对象的unique ID
                    clearTimeout(_ts.config.control_timer)
                    clearInterval(_c.timer)
                    var touch = e.touches[0],
                        scale = 0,
                        L = (touch.clientX - $progress.offset().left);
                    //oVideo.pause();
                    if (L < 0) {
                        L = 0;
                    } else if (L > $progress.width()) {
                        L = $progress.width();
                    }
                    scale = L / $progress.width();
                    $bar.css('width', scale * 100 + '%');
                    $progress.data('startx', touch.clientX).data('startw', $bar.width());

                    //oVideo.currentTime = scale * oVideo.duration;
                    _ts.nowTime($modVideoCon, oVideo, scale);
                });
                touch.on($progress, 'touchmove touchend', function (e) {
                    var ts = $(this);
                    setTimeout(function () {
                        var touch = e.touches[0],
                            startx = $progress.data('startx'),
                            startw = $progress.data('startw'),
                            scale = 0,
                            L = (touch.clientX - startx) + startw;
                        if (L < 0) {
                            L = 0;
                        } else if (L > $progress.width()) {
                            L = $progress.width();
                        }

                        scale = L / $progress.width();
                        $bar.css('width', scale * 100 + '%');
                        //oVideo.currentTime = scale * oVideo.duration;
                        _ts.nowTime($modVideoCon, oVideo, scale);
                    }, 80);
                });
                touch.on($progress, 'touchend', function (e) {
                    setTimeout(function () {
                        var scale = $bar.width() / $progress.width();
                        oVideo.currentTime = scale * oVideo.duration;
                        // oVideo.play();
                    }, 80);
                    _ts.config.control_timer = setTimeout(function () {
                        $('.zk_videoplayer_controlwrapper', $modVideoCon).css('opacity', '0');
                    }, 3000)
                    _c.timer = setInterval(function () {
                        _ts.nowTime($modVideoCon, oVideo);
                    }, 1000);
                });
            }
        },
        launchIntoFullscreen: function (element) {
            if (element.requestFullscreen) {
                element.requestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen();
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            } else if (element.webkitEnterFullscreen || element.enterFullScreen) {
                element.webkitEnterFullscreen && element.webkitEnterFullscreen();
                element.enterFullScreen && element.enterFullScreen();
            }
        },
        exitFullscreen: function () {
            //退出全屏
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.oRequestFullscreen) {
                document.oCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        },
        zkvideo_info: function (width, height) {
            var url = 'http://www.myzaker.com/download_app.php';
            var cmd_arg = {
                "client_params": {
                    "video_height": height,
                    "video_width": width
                }
            }
            cmd_arg = JSON.stringify(cmd_arg);
            cmd_arg = encodeURIComponent(cmd_arg);
            cmd_arg = url+'?_zkcmd=videoinfo&cmd_arg='+cmd_arg;
            console.log('调用cmd：', cmd_arg);
            window.location.href = cmd_arg;
        },
        stat: function (url) {
            if(!url) return
            new Image().src = url
        }
    }
};

//入口
TalMain.isJQReady(function () {
    $(function () {
        TalMain.init();
    });
});