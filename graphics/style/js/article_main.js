//
////图片初始化
//function imgIntit() {
//	var imglazy = document.getElementsByClassName('lazy');
//	for (var i = 0; i < imglazy.length; i++) {
//		var w = parseInt(imglazy[i].getAttribute('width'));
//		var h = parseInt(imglazy[i].getAttribute('height'));
//
//		if (!(w > 0 && h > 0)) {
//			w = parseInt(imglazy[i].getAttribute('data-width'));
//			h = parseInt(imglazy[i].getAttribute('data-height'));
//		}
//		if (w > 0 && h > 0) {
//			var rdw = document.getElementById("content").clientWidth;
//			var rw = w < rdw ? w : rdw;
//			var rh = rw * (h / w);
//			imglazy[i].setAttribute('width', Math.ceil(rw));
//			imglazy[i].setAttribute('height', Math.ceil(rh));
//		}
//	}
//};
//imgIntit();
//
//

var loading = false;
var show_comment_num_first = 5;
$(".pr").each(function (index) {
    if (index > show_comment_num_first - 1) {
        $(this).addClass('none');
    }
});
function makeUrlWithArg(url, arg) {
    if (url == null || typeof url == 'undefined') {
        return '';
    }

    var p = (url.indexOf('?') > 0) ? "&" : "?";
    var qstr = "";
    var tmp_p = "";
    $.each(arg, function (t_key, t_val) {
        if (t_key == "" || t_val == "") {
            return;
        }
        qstr += tmp_p + t_key + "=" + encodeURIComponent(t_val);
        tmp_p = "&";
    });
    if (qstr != "") {
        url += p + qstr;
    }

    return url;
}

var tempCommentArr = Array();
function getComment() {
    if (loading == true)
        return;
    if (tempCommentArr.length < 5) {
        loading = true;
        $(".changeTop").html('正在加载...');
        //var next_url = encodeURIComponent($("#next_url").val());
        //var c_url = "article_action.php?act=nextUrl&url="+next_url;
        var c_url = makeUrlWithArg(CONFIG['article_action_url'], {act: "nextUrl", url: $("#next_url").val()});
        $.getJSON(c_url, function (json) {
            if (json) {
                tempCommentArr = tempCommentArr.concat(json['list']);
                if (json['next_url']) {
                    $("#next_url").val(json['next_url']);
                } else {
                    $("#next_url").val("");
                    if (tempCommentArr.length <= 5)
                        $(".changeTop").remove();
                }

                addComment();
                loading = false;
                $(".changeTop").html('查看更多评论');
            }
        });
    } else {
        addComment();
        if ($("#next_url").val() == '') {
            $(".changeTop").remove();
        }
    }
}

function addComment() {
    var loop = tempCommentArr.length < 5 ? tempCommentArr.length : 5;
    var json = tempCommentArr;
    comment_list_html = "";
    for (var i = 0; i < loop; i++)
    {
        var tmp = json.shift();
        tmp['like_num'] = tmp['like_num'] == 0 ? '' : tmp['like_num'];
        if (tmp['reply_comment']) {
            tmp['reply'] = '<div class="reply">' + tmp['reply_comment']['auther_name'] + '：' + tmp['reply_comment']['content'] + '</div>';
        } else {
            tmp['reply'] = '';
        }

        tmp['data_original'] = 'src';
        comment_list_html += template($('#comment_li').val(), tmp);
    }

    $('.new_comment_box_all').append(comment_list_html);
    //$('.tx_r').append(comment_list_html);
}

if (!browser.versions.iPhone)
{
    var pre_scroll = -1;
    var downFlag = true;
    var is_animate = false;
    $(window).scroll(function () {
        if ($(window).scrollTop() < 50) {
            showDownloadBtn();
            downFlag = true;
        } else
        {
            if ((pre_scroll - $(window).scrollTop()) > 0) {
                if (!downFlag && !is_animate)
                {
                    is_animate = true;
                    showDownloadBtn();
                }
                downFlag = true;
            } else
            {
                if (downFlag && !is_animate)
                {
                    is_animate = true;
                    hideDownloadBtn();
                }
                downFlag = false;
            }

            pre_scroll = $(window).scrollTop();
        }
    });
    var bt;
    var t;
    function touchEnd(event) {
        if (t)
            clearTimeout(t);
        t = setTimeout('touchendTimeOut()', 500);
    }

    var touchStarY;
    function touchendTimeOut() {
        if ($(window).scrollTop() < 50) {
            showDownloadBtn();
            downFlag = true;
        }
    }

    document.addEventListener("touchend", touchEnd, false);
} else {
    document.ontouchstart = function (e) {
        touchStarY = e.targetTouches[0].pageY;
    };
    document.ontouchmove = function (e) {
        nStartY = e.targetTouches[0].pageY;
        var distanceY = touchStarY - nStartY;
        if (Math.abs(distanceY) > 10) {
            if ($(window).scrollTop() > 50) {
                if (distanceY > 0) {
                    $("#downTips").css('top', "-42px");
                } else {
                    $("#downTips").css('top', "0");
                }
            } else {
                $("#downTips").css('top', "0");
            }
        }
    };
}

var likeArr = [];
function zan(obj) {
    var cid = $(obj).parent().find("input[name=cid]").val();
    var _this = obj;
    if (likeArr.indexOf(cid) == -1) {
        //var c_url = "article_action.php?act=like_com&cid="+cid+"&pk="+CONFIG['pkid'];
        var c_url = makeUrlWithArg(CONFIG['article_action_url'], {act: "like_com", cid: cid, pk: CONFIG['pkid']});
        $.getJSON(c_url, function (json) {
            if (json.stat == 1) {
                var num = Number($(_this).parent().find(".like_num").html());
                var like_num = $(_this).parent().find(".like_num");
                $(like_num).show();
                if (!isNaN(num)) {
                    $(like_num).html(num + 1);
                }
                $(_this).css("border-color", "#fb4747");
                $(_this).css("color", "#fb4747");
                $(_this).addClass("active");
                $(_this).find("img").attr("src", "dist/images/like_bg_2.png");
                $(_this).find(".like").addClass("active");
                likeArr.push(cid);
            } else
            {
                alert(json.msg);
            }
        });
    }
}

function stopBubble(e) {
    var e = e ? e : window.event;
    if (window.event) { // IE  
        e.cancelBubble = true;
    } else { // FF  
        e.stopPropagation();
    }
}

var reply_str = '';
function showInput(obj) {
    var cid = '';
    if (obj) {
        var author = $(obj).find('.author').text();
        var content = $(obj).find('.con').text();
        $("#comment-input-id").attr("placeholder", '回复:' + author);
        cid = $(obj).find("input[name=cid]").val();
        $("#reply_cid").val(cid);
        reply_str = author + ':' + content;
    }

    $("#comment-input-id").focus()
}

if (browser.versions.isPc) {
    $(".div-like").one('click', function (e) {
        addLikeFun(this);
        $(this).addClass("active");
    })
} else {
    $(".div-like").one('touchend', function (e) {
        addLikeFun(this);
        $(this).addClass("active");
    })
}

function addLikeFun() {
//var c_url = "article_action.php?act=like_art&pk="+CONFIG['pkid']+"&app_id="+CONFIG['app_id'];
    var c_url = makeUrlWithArg(CONFIG['article_action_url'], {act: "like_art", app_id: CONFIG['app_id'], pk: CONFIG['pkid']});
    $.getJSON(c_url);
    $('#add_like').css('display', '-webkit-box');
    $('#add_like').animate({'translateY': '-200%'}, 400, function (e) {
        $('#add_like').hide();
    })
}

var inputDom = $("#comment-input-id");
var focusFlag = false;
inputDom.focus(function () {
    focusFlag = true;
});
document.addEventListener('touchmove', function (e) {
    if (focusFlag) {
        focusFlag = false;
        inputDom.attr("placeholder", "我也评论一句");
        $("#reply_cid").val('');
        document.activeElement.blur();
    }
}, false);
$("#comment-input-btn").click(function (e) {

    if (inputDom.val() !== '')
    {
        var obj = {};
        obj['auther_icon'] = 'https://sns.myzaker.com/images/noavatar_middle.png';
        obj['auther_name'] = '我';
        obj['like_num'] = '';
        obj['ctime'] = '刚刚';
        obj['content'] = inputDom.val();
        if ($("#reply_cid").val()) {
            obj['reply'] = '<div class="reply">' + reply_str + '</div>';
        } else {
            obj['reply'] = '';
        }

        obj['data_original'] = 'src';
        var comment_list_html = template($('#comment_li2').val(), obj);
        $('.comment-input-box').after(comment_list_html).show();
        var distance = $('.comment-input-box').offset().top;
        $('body').animate({scrollTop: distance}, 500, function () {
            inputDom.focus();
        });
        //var c_url = "article_action.php?act=commentPost&pk="+CONFIG['pkid'];
        var c_url = makeUrlWithArg(CONFIG['article_action_url'], {act: "commentPost", pk: CONFIG['pkid']});
        $.post(c_url, {content: inputDom.val(), cid: $("#reply_cid").val()});
        inputDom.attr("placeholder", "我也评论一句");
        inputDom.val('');
        $("#reply_cid").val('');
    }

});
function template(_str, _arr)
{
    //替换模板变量
    var reCat = /<{(\w+)}>/gi;
    return _str.replace(reCat, function () {
        return _arr[arguments[1]];
    });
}

var topHeight = $("#downTips").height();
function showDownloadBtn() {
    if (browser.versions.iPhone) {
        $('#downTips').show();
        is_animate = false;
    } else {
        $('#downTips').animate({opacity: 1}, 'slow', function (e) {
            is_animate = false;
        });
    }
}

function hideDownloadBtn() {
    //  $('#downTips').hide();
    if (browser.versions.iPhone) {
        $('#downTips').hide();
        is_animate = false;
    } else {
        $('#downTips').animate({opacity: 0}, 'slow', function (e) {
            is_animate = false;
        });
    }
}


$(".changeTop").on('click', function (e) {
    $(".pr.none").each(function (index) {
        if (index < 5) {
            $(this).removeClass('none');
        }
    });
    if ($(".pr.none").length == 0)
        getComment();
});
function ucShare(config) {
    if (window.navigator.userAgent.indexOf("UCBrowser") !== -1) {
        var fn = function (origin) {
            var isIOS = window.navigator.userAgent.indexOf('iPhone') !== -1,
                    isAndroid = window.navigator.userAgent.indexOf('Android') !== -1,
                    tmppc1 = document.getElementById("news_template_04_banner"),
                    tmppc2 = document.getElementById("id_imagebox_0"),
                    picId = tmppc1 ? tmppc1 : tmppc2 ? tmppc2 : '';
            if (isIOS) {
                var target = origin == 'friend' ? 'kWeixin' : 'kWeixinFriend';
                linkPlus = origin == 'friend' ? '&f=weixin_uc_friend' : '&f=weixin_uc_timeline';
                ucbrowser.web_share(config.title, config.desc, config.link + linkPlus, target, '', '', picId.id);
            } else if (isAndroid) {
                var target = origin == 'friend' ? 'WechatFriends' : 'WechatTimeline';
                linkPlus = origin == 'friend' ? '&f=weixin_uc_friend' : '&f=weixin_uc_timeline',
                        getPos = {
                            getTop: function (e) {
                                var offset = e.offsetTop;
                                if (e.offsetParent != null)
                                    offset += getPos.getTop(e.offsetParent);
                                return offset;
                            },
                            getLeft: function (e) {
                                var offset = e.offsetLeft;
                                if (e.offsetParent != null)
                                    offset += getPos.getLeft(e.offsetParent);
                                return offset;
                            },
                            getNodeInfoById: function (e) {
                                var myNode = document.getElementById(e);
                                if (myNode) {
                                    var
                                            pos = [getPos.getLeft(myNode), getPos.getTop(myNode), myNode.offsetWidth, myNode.offsetHeight]
                                    return (pos)
                                } else {
                                    return false
                                }
                            }
                        }

                ucweb.startRequest('shell.page_share', [config.title, config.desc, config.link + linkPlus, target, '', '', getPos.getNodeInfoById(picId.id)])
            }
        };
        $(".share-wxfriend").on('touchstart', function () {
            $(this).addClass("active");
        }).on('touchend', function () {
            $(this).removeClass("active");
            fn('friend');
        });
        $(".share-wxtimeline").on('touchstart', function () {
            $(this).addClass("active");
        }).on('touchend', function () {
            $(this).removeClass("active");
            fn('timeline');
        });
    }
}

var do_wx_share_stat = function () {
    var stat_url = CONFIG['wx_share_stat_url'];
    if (stat_url) {
        var img = new Image();
        img.src = stat_url;
    }
}

setTimeout(function () {
    if (window.navigator.userAgent.indexOf("MicroMessenger") >= 0) {
        var scriptdom = document.createElement('script');
        scriptdom.type = 'text/javascript';
        scriptdom.src = 'https://app.myzaker.com/tools/wx.php?v=1';
        document.body.appendChild(scriptdom);
        var _this = this;
        scriptdom.onload = function () {
            wxShare.init(CONFIG['wx_share_title'], CONFIG['wx_share_desc'], CONFIG['wx_share_link'], CONFIG['img_url'], do_wx_share_stat);
     
            //微信图片预览
                $(document).on('click', '#content .perview_img_div',function(event) {
                    var imgArray = [];
                    var curImageSrc = $(this).children("img").attr('data-original')?$(this).children("img").attr('data-original'):$(this).children("img").attr('src');
                    var oParent = $(this).children("img").parent();
                    if (curImageSrc && !oParent.attr('href')) {
                        $('#content .perview_img_div img').each(function(index, el) {
                            var itemSrc =  $(this).attr('data-original')?$(this).attr('data-original'):$(this).attr('src');
                            imgArray.push(itemSrc);
                        });
                        wx.previewImage({
                            current: curImageSrc,
                            urls: imgArray
                        });
                    }
                });
 
        }
    }

    ucShare({title: CONFIG['wx_share_title'], desc: CONFIG['wx_share_desc'], link: CONFIG['wx_share_link'], img: CONFIG['img_url']});
}, 2000);

function addHtml() {
    if (topJson == null) {
        return;
    }
    var html = "";
    var tmpArr = Array();
    var more = 0;
    var fix_position;

    if (topJson.topic && topJson.topic instanceof Array) {
        if (topJson.topic.length == 0) {
            more++;
        } else {
            var topic = topJson.topic.shift();
            if (topic) {
                html += "<div class='relate'>";
                html += "<a href='" + makeUrlWithArg(topic.url, {f: CONFIG['req_f']}) + "'>";
                html += "<span class='relate-title'><div class='topic-title'>" + topic.title + "(" + topic.timeline + ")</div><img class='topic-icon' src='//zkres.myzaker.com/data/image/mark/topic_2x.png' width='23'></span>";
                html += "<div class='border'><div class='icon' style='background-image:url(" + topic.img_url + ");'></div></div>";
                html += "</a></div>";
                tmpArr.push(html);
            }
        }

    }

    if (topJson.local && topJson.local instanceof Array) {
        var loop = topJson.local.length < 3 ? topJson.local.length : 3;
        if (topJson.local.length < 3) {
            more = more + (3 - topJson.local.length);
        } else if (topJson.top.length > 7) {
            for (var i = 0; i < loop; i++) {
                var local = topJson.local.shift();
                if (local) {
                    if (local.id == CONFIG['pkid'])
                        continue;
                    html = "<div class='relate'>";
                    html += "<a href='" + makeUrlWithArg(local.url, {f: CONFIG['req_f']}) + "'>";
                    html += "<span class='relate-title'><div class='topic-title'>" + local.title + "</div><div  class='topic-source'>" + local.author + "&nbsp;&nbsp;" + local.time + "&nbsp;&nbsp;<img class='topic-icon' src='dist/images/icon_local.png' width='23'></div></span>";
                    html += "<div class='border'><div class='icon' style='background-image:url(" + local.img + ");'></div></div>";
                    html += "</a></div>";
                    tmpArr.push(html);
                }
            }
        }
    }

    var topArr = Array();
    if (topJson.top && topJson.top instanceof Array) {

        var loop = topJson.top.length < 7 ? topJson.top.length : 7 + more;
        for (var i = 0; i < loop; i++) {
            var top = topJson.top.shift();
            if (top) {
                if (top.pk == CONFIG['pkid'])
                    continue;
                topArr.push(top);
            }
        }

        //小图模板
        for (var i = 0; i < topArr.length; i++) {
            var top = topArr[i];
            if (top) {
                html = "<div class='relate'>";
                html += "<a href='" + makeUrlWithArg(top.url, {f: CONFIG['req_f']}) + "'>";
                  html += "<span class='relate-title'><div class='topic-title'>" + top.title + "</div><div  class='topic-source'>" + top.author + "&nbsp;&nbsp;" + top.time + "</div></span>";
                if (top.cover.url) {
                    html += "<div class='border'><div class='icon' style='background-image:url(" + top.cover.url + ");'></div></div></a></div>";
                } else {
                    html += "<div class='border'><div class='icon' style='background-color:" + top.cover.color + ";background-size: cover;'>" + top.cover.font + "</div></div></a></div>";
                }

              

                tmpArr.push(html);
            }
        }
    }
    
       var newTopArr = Array();
    if (topJson.newtop && topJson.newtop instanceof Array) {

        for (var i = 0; i < topJson.newtop.length; i++) {
            var newtop = topJson.newtop[i];
            if (newtop.fix_position == 1) {
                fix_position = newtop;
                break;
            }
        }

        var loop = 32;//topJson.newtop.length;
        for (var i = 0; i < loop; i++) {
            var newtop = topJson.newtop.shift();
            if (newtop) {
                if (newtop.pk == CONFIG['pkid'])
                    continue;
                newTopArr.push(newtop);
            }
        }

        //小图模板
        for (var i = 0; i < newTopArr.length; i++) {
            var newtop = newTopArr[i];
            if (newtop) {
                html = "<div class='relate'>";
                html += "<a href='" + makeUrlWithArg(newtop.url, {f: CONFIG['req_f']}) + "'>";
                   html += "<span class='relate-title'><div class='topic-title'>" + newtop.title + "</div><div  class='topic-source'>" + newtop.author + "&nbsp;&nbsp;" + newtop.time + "</div></span>";
                if (newtop.cover.url) {
                    // html += "<div class='border'><div class='icon' style='background-image:url(" + newtop.cover.url + ");'></div></div></a></div>";
                    // 图片懒加载
                    html += "<div class='border'><img class='lazy opacity_0 icon' style='object-fit: cover;' data-original='"+ newtop.cover.url +"' /></div></a></div>"
                } else {
                    html += "<div class='border'><div class='icon' style='background-color:" + newtop.cover.color + ";background-size: cover;'>" + newtop.cover.font + "</div></div></a></div>";
                }

             

                tmpArr.push(html);
            }
        }
    }
    //实现3个相关文章，第四个广告
    var dspArr = Array();
    if (topJson.dsp && topJson.dsp instanceof Array) {
        var loop = topJson.dsp.length;
        for (var i = 0; i < loop; i++) {
            var dsp = topJson.dsp.shift();

            if (dsp) {
                html = "<div class='relate'>";

                // 默认广告tag地址
                var tagImg = '//zkres.myzaker.com/data/image/mark2/ad_2x.png';

                switch (dsp.item_type) {
                    case "3_b":
                        html = "<div class='relate three_pic'>";
                        break;
                    case "1_b":
                        html = "<div class='relate big_pic'>";
                        break;
                    case "1_f":
                        html = "<div class='relate big_pic_notitle'>";
                        tagImg = dsp.tag_image;
                        break;
                    case 1:
                    case "1":
                    default:
                }
                
                var dsp_stat_info = dsp.dsp_stat_info;

                var id_name = "dsp_id_" + Math.floor(Math.random() * 100000000000);

                html += "<a href='" + dsp.web_url + "' id='"+ id_name +"'>";

                // 通过点击统计
                (function(id_name,dsp_stat_info){
                    setTimeout(function(){
                        $('#' + id_name).click(function(){
                            if(!dsp_stat_info.click_stat_urls){
                                return;
                            }
                            // 为了防止a标签跳链接导致的统计失败，先统计后走a标签默认逻辑
                            var request = new XMLHttpRequest();
                            request.open('GET', dsp_stat_info.click_stat_urls, false); 
                            request.send(null);
                        });

                        // 绑定window的scroll事件，在滚动到当前区域的时候曝光
                        var isTime = 0;
                        var scrollFun = function(){
                            if(isTime){
                                return;
                            }
                            // 目标元素
                            var tarEle = $('#' + id_name);

                            // 获取元素定位
                            var positionTop = tarEle.parent('.relate').position().top;

                            // 获取元素高度
                            var eleHeight = tarEle.parent('.relate').height(); 

                            // 当前滚动值
                            var scrolltop = window.scrollTop;

                            // 获取屏幕高度
                            var winHeight = $(window).height();

                            // 当达到滚动值时，添加曝光统计
                            if((scrolltop + winHeight > eleHeight + positionTop) && (scrolltop <  positionTop)){
                                (new Image()).src = (dsp.stat_read_url || dsp_stat_info.show_stat_urls || "");
                                $(window).off("scroll",scrollFun);
                            }

                            isTime = 1;
                            setTimeout(function(){
                                isTime = 0;
                            },500);
                        };
                        $(window).on("scroll",scrollFun);

                        // 曝光逻辑分离
                        // (new Image()).src = (dsp.stat_read_url || dsp_stat_info.show_stat_urls);
                    },10);
                })(id_name,dsp_stat_info);

                // html += "<span class='relate-title'><div class='topic-title'>" + dsp.title + "</div><img src='" + (dsp.stat_read_url || dsp_stat_info.show_stat_urls) + "' style='display:none' ><img class='topic-icon' src='"+ tagImg +"' width='23'></span>";
                html += "<span class='relate-title'><div class='topic-title'>" + dsp.title + "</div><img class='topic-icon' src='"+ tagImg +"' width='23'></span>";

                if (dsp.img) {
                    // 图片懒加载
                    html += "<div class='border'><img class='lazy opacity_0 icon' style='object-fit: cover;' data-original='"+ dsp.img +"' /></div></a></div>";
                    // html += "<div class='border'><div class='icon' style='background:url(" + dsp.img + ");background-size: cover;'></div></div></a></div>";
                } else if (dsp.thumbnail_medias && dsp.thumbnail_medias.length) {
                    dsp.thumbnail_medias.forEach(function(e) {
                        // 图片懒加载
                        // html += "<div class='border'><div class='icon' style='background-image:url(" + e.url + ")'></div></div>";
                        html += "<div class='border'><img class='lazy opacity_0 icon' style='object-fit: cover;' data-original='"+ e.url +"' /></div>";
                    });
                } else if (dsp.cover) {
                    html += "<div class='border'><div class='icon' style='background-color:" + dsp.cover.color + ";background-size: cover;'>" + dsp.cover.font + "</div></div></a></div>";
                }

                // 添加收尾
                html += "</a></div>";

                //topJson.top.push(top);
                dspArr.push(html);
            }
        }
    }

    //相关文章
    var relArr = Array();
    
    if (topJson.rel && topJson.rel instanceof Array) {

        var loop = 3;
        for (var i = 0; i < loop; i++) {
            var rel = topJson.rel.shift();

            if (rel) {
                html = "<div class='relate'>";
                html += "<a href='" + rel.article.weburl + "'>";
                     html += "<span class='relate-title'><div class='topic-title'>" + rel.title + "</div><div  class='topic-source'>" + rel.author + "&nbsp;&nbsp;" + rel.time + "</div></span>";
                if (rel.img) {
                    html += "<div class='border'><div class='icon' style='background:url(" + rel.img + ");background-size: cover;'></div></div></a></div>";
                } else {
                    html += "<div class='border'><div class='icon' style='background-color:" + rel.cover.color + ";background-size: cover;'>" + rel.cover.font + "</div></div></a></div>";
                    //   html += "<div class='border'><div class='icon' style='background-color:#ff6363;background-size: cover;'>光</div></div>";//缺少参数
                }
           

                relArr.push(html);
            }
        }
    }

    if (tmpArr.length > 0) {
        tmpArr.sort(function () {
            return 0.5 - Math.random()
        });
        if (relArr.length > 0) {

            for (var i = 0; i < relArr.length; i++) {
                tmpArr.splice(i, 0, relArr[i]);
            }

        }
        if (dspArr.length > 0) {
            // 获取位置
            var ad_position = topJson.ad_position;

            // 根据定位设定广告的位置
            if(ad_position && ad_position.length){
                ad_position.forEach(function(e,i){
                    tmpArr.splice(e - 1, 0, dspArr[i]);
                });
            }else{
                // 旧的添加位置逻辑
                for (var i = 0; i < dspArr.length; i++) {
                    tmpArr.splice(3 + i * 2, 0, dspArr[i]);
                }
            }
        }

        if (fix_position) {
            var newtop = fix_position;
            var html = "<div class='relate'>";
            html += "<a href='" + makeUrlWithArg(newtop.url, {f: CONFIG['req_f']}) + "'>";
            html += "<span class='relate-title'><div class='topic-title'>" + newtop.title + "</div><div  class='topic-source'>" + newtop.author + "&nbsp;&nbsp;" + newtop.time + "</div></span>";
            if (newtop.cover.url) {
                html += "<div class='border'><div class='icon' style='background-image:url(" + newtop.cover.url + ");'></div></div></a></div>";
            } else {
                html += "<div class='border'><div class='icon' style='background-color:" + newtop.cover.color + ";background-size: cover;'>" + newtop.cover.font + "</div></div></a></div>";
            }

            tmpArr.splice(3, 1, html);
        }

        html = tmpArr.join("");
        $("#top5").append(html);
        try{
            window.lazyLoad.init();
        }catch(err){ };
    } 
//    else {
        $('.loading').remove();
        $('.uc-addtop-btn').remove();
//    }
}

//青柠浏览器的评论框要置顶
if (CONFIG['f'] == 'mycheering') {
    $(".commentInputBox").removeClass('addbottom');
    $(".commentInputBox").addClass('addtop');
}

setTimeout(function () {
    var img = new Image();
    img.src = CONFIG['stat_url'];
}, 1000);
setTimeout(function () {
    var img = new Image();
    img.src = CONFIG['baiduHm'];
}, 1200);
setTimeout(function () {
    var img = new Image();
    img.src = CONFIG['cnzz'];
}, 1400);
//懒加载触犯
setTimeout(function () {
    if ($(window).scrollTop == 0) {
        window.scrollTo(0, 0);
    }
}, 500);

//中间内容广告
if(CONFIG['isShowContentAds'] == 1 && CONFIG['content_ad_url'] != '' && $('#article_middle')){
    setTimeout(function () {
        $.ajax({
            type: 'GET',
            url: CONFIG['content_ad_url'],
            dataType: 'jsonp',
            timeout: 2000,
            success: function (ret) {
                if(ret.code == 0 && ret.data.length>0){
                    var time = null,
                        mlogFun = function(url){
                        var img = new Image();
                        //img.referrerPolicy = 'no-referrer';
                        img.src = url;
                    },
                    showMlogFun = function(urls){
                        urls = data.show_urls;
                        clearTimeout(time);
                        time = setTimeout(function(){
                            var _top = $('#article_middle').offset().top - $(window).scrollTop();
                            if( _top > 0 && _top <= $(window).height()/2+50){
                                for(var i=0; i<urls.length; i++){ mlogFun(urls[i]); }
                                document.removeEventListener('scroll', showMlogFun, false);
                            }
                        }, 300);
                    };
                    var data = ret.data[0],
                        $head = document.getElementsByTagName("head")[0],
                        $el = $('#article_middle'),
                        html = '<a href="'+data.ads_link_url+'" style="text-decoration: none; cursor: pointer;">\
                            <img src="'+data.imgs[0]+'" alt="" />\
                            <span class="tit">'+data.ads_name+'</span>\
                            <span class="tips" style="background-image: url('+data.icon+');"></span>\
                        </a>',
                        style = '<style>\
                            #article_middle{ position: relative; overflow: hidden; border: 1px solid #dfdfdf;border-radius: 6px;}\
                            #article_middle img{display: block; overflow: hidden; width: 100%; height: auto;border-top-left-radius: 6px; border-top-right-radius: 6px;}\
                            #article_middle .tit{ display: block; padding-left: 15px; line-height: 34px; color: #3b3b3b; font-size: 14px;}\
                            #article_middle .tips{position: absolute; bottom: 8px; right: 15px; display:block; width: 35px; height: 20px; background-size: 100% auto; background-repeat: no-repeat;}\
                            </style>';
                    $head.innerHTML = $head.innerHTML+style;
                    $el.html(html);
                    $el.click(function(){
                        for(var i=0; i<data.click_urls.length; i++){ mlogFun(data.click_urls[i]); }
                    });
                    //监听滚动到广告可视区就统计
                    showMlogFun(data.show_urls);
                    document.addEventListener('scroll', showMlogFun, false);
                }
            },
            error: function (xhr, type) {}
        });
    }, 500);
}

//add by liujinwei 合拼广告接口
 var topJson = {};
if (CONFIG['isShowAds']) {

   
    setTimeout(function () {
        $.ajax({
            type: 'GET',
            url: CONFIG['ad_url'],
            dataType: 'jsonp',
            timeout: 2000,
            success: function (data) {

                //百度广告
                if(data.data.article_bottom.baidu_js_ad && $('#article_bottom').length>0){
                    var baidu_pos_id = data.data.article_bottom.baidu_js_ad.pos_id;
                    var s = "_" + Math.random().toString(36).slice(2);
                    $('#article_bottom').html('<div style="" id="' + s + '"></div>');
                    (window.slotbydup = window.slotbydup || []).push({ id: baidu_pos_id, container: s });
                }

                getDspAd(data.data.article_bottom.list[0].js_url);
                
                if (data.data.recommend.length > 0) {
                        var loop = data.data.recommend.length;
                        for (var i = 0; i < loop; i++) {
                            var recommendData = data.data.recommend.shift();
                            if (recommendData.rd_id == 'dsp') {
                                var dspRecommend = recommendData;
                            } else {
                                var relRecommend = recommendData;
                            }
                        }
                    }
                    
                if (CONFIG['isTop5']) {
                    setTimeout(function () {

                        if ($.fn.cookie("zaker_my_city_2")) {
                            getLocal($.fn.cookie("zaker_my_city_2"));
                            getRecommend();
                            getRelateAt(relRecommend);
                            getDspRecommend(dspRecommend);
                        } else {
                            //var url = "article_recommend.php?act=city";
                            var url = makeUrlWithArg(CONFIG['article_recommend_url'], {act: "city"});
                            $.get(url, function (cityname) {
                                var date = new Date();
                                date.setTime(date.getTime() + (2 * 60 * 60 * 1000));
                                $.fn.cookie("zaker_my_city_2", cityname, {path: '/', expires: date});
                                getLocal(cityname);
                                getRecommend();
                                getRelateAt(relRecommend);
                                getDspRecommend(dspRecommend);
                            })
                        }


                    }, 200);
                }
           
                if(CONFIG['isRelate']){
                    $.getJSON(CONFIG['article_relate_url'], function (article) {
                      
                    getRelatedList(article,dspRecommend);
                 });
                }

                if (CONFIG['isWonderful']) {
                    $.getJSON(CONFIG['article_wonderful_url'], function(article) {
                        appendHtmlWonderfulList(article, dspRecommend);
                    });
                }

            },
            error: function (xhr, type) {
                setTimeout(function () {
                    
                                if ($.fn.cookie("zaker_my_city_2")) {
                                    getLocal($.fn.cookie("zaker_my_city_2"));
                                    getRecommend();
                    
                                } else {
                                    //var url = "article_recommend.php?act=city";
                                    var url = makeUrlWithArg(CONFIG['article_recommend_url'], {act: "city"});
                                    $.get(url, function (cityname) {
                                        var date = new Date();
                                        date.setTime(date.getTime() + (2 * 60 * 60 * 1000));
                                        $.fn.cookie("zaker_my_city_2", cityname, {path: '/', expires: date});
                                        getLocal(cityname);
                                        getRecommend();
                    
                                    })
                                }
                    
                    
                            }, 200);
            }
        });
    }, 700);
}else{
    if (CONFIG['isWonderful']) {
        $.getJSON(CONFIG['article_wonderful_url'], function(article) {
            appendHtmlWonderfulList(article, '');
        });
    } else {
        setTimeout(function () {

            if ($.fn.cookie("zaker_my_city_2")) {
                getLocal($.fn.cookie("zaker_my_city_2"));
                getRecommend();

            } else {
                //var url = "article_recommend.php?act=city";
                var url = makeUrlWithArg(CONFIG['article_recommend_url'], {act: "city"});
                $.get(url, function (cityname) {
                    var date = new Date();
                    date.setTime(date.getTime() + (2 * 60 * 60 * 1000));
                    $.fn.cookie("zaker_my_city_2", cityname, {path: '/', expires: date});
                    getLocal(cityname);
                    getRecommend();

                })
            }


        }, 200);
    }
}

    function appendHtmlWonderfulList(article, dspRecommend) {
        var html = '';

        var wonderfulArticles = article.data;
        for (var index = 0; index < wonderfulArticles.length; index++) {
            //精彩推荐文章
            var val = wonderfulArticles[index];

            if (val) {
                html += "<div class='relate'>";
                html += "<a href='" + makeUrlWithArg(val.url, {f: CONFIG['req_f']}) + "'>";
                if (val.cover) {
                    html += "<span class='relate-title'><div class='topic-title'>" + val.title + "</div><div class='topic-source'>" + val.author + "</div></span>";
                }

                html += "<div class='border'><div class='icon' style='background-image: url(" + val.cover + ");'></div></div></a></div>";
            }
        }

        //dsp广告
        if (dspRecommend.list && dspRecommend.list instanceof Array) {
            var loop = 1;
            for (var i = 0; i < loop; i++) {
                var dsp = dspRecommend.list.shift();

                if (dsp) {
                    html += "<div class='relate'>";
                    html += "<a href='" + dsp.web_url + "'>";
                    html += "<span class='relate-title'><div class='topic-title'>" + dsp.title + "</div><img src='" + dsp.stat_read_url + "' style='display:none' ><img class='topic-icon' src='//zkres.myzaker.com/data/image/mark/ad_2x.png' width='23'></span>";
                    if (dsp.img) {
                        html += "<div class='border'><div class='icon' style='background:url(" + dsp.img + ");background-size: cover;'></div></div>";
                    } else {
                        html += "<div class='border'><div class='icon' style='background-color:" + dsp.cover.color + ";background-size: cover;'>" + dsp.cover.font + "</div></div>";
                        // html += "<div class='border'><div class='icon' style='background-color:#ff6363;background-size: cover;'>光</div></div>";   //缺少参数
                    }

                    html += "</div>";

                }
            }
        }

        $('#top5').append(html);
        $('.loading').remove();
        $('.uc-addtop-btn').remove();
        try{
            window.lazyLoad.init();
        }catch(err){ };
    }

    //获取大图广告
    function getDspAd(ad_url) {
        var adScriptdom = document.createElement('script');
        adScriptdom.type = 'text/javascript';
        adScriptdom.src = ad_url;
        document.body.appendChild(adScriptdom);
    }
    //相关推荐列表
    function getRelatedList(article,dspRecommend){
            var html='';
            //dsp广告
            if (dspRecommend.list && dspRecommend.list instanceof Array) {
                var loop = 1;
                for (var i = 0; i < loop; i++) {
                    var dsp = dspRecommend.list.shift();

                    if (dsp) {
                        html += "<div class='relate'>";
                        html += "<a href='" + dsp.web_url + "'>";
                        if (dsp.img) {
                            html += "<div class='border'><div class='icon' style='background:url(" + dsp.img + ");background-size: cover;'></div></div>";
                        } else {
                            html += "<div class='border'><div class='icon' style='background-color:" + dsp.cover.color + ";background-size: cover;'>" + dsp.cover.font + "</div></div>";
                            // html += "<div class='border'><div class='icon' style='background-color:#ff6363;background-size: cover;'>光</div></div>";   //缺少参数
                        }

                        html += "<span class='relate-title'><div class='topic-title'>" + dsp.title + "</div><img src='" + dsp.stat_read_url + "' style='display:none' ><img class='topic-icon' src='//zkres.myzaker.com/data/image/mark/ad_2x.png' width='23'></span></a></div>";
       
                    }
                }
            }

            //相关文章
            if (article.rel && article.rel instanceof Array) {

                var loop = article.rel.length;
                for (var i = 0; i < loop; i++) {
                    var rel = article.rel.shift();

                   if (rel) {  
                        html += "<div class='relate'>";
                        html += "<a href='" + makeUrlWithArg(rel.url, {f: CONFIG['req_f']}) + "'>";
                        if (rel.cover.url) {
                            html += "<div class='border'><div class='icon' style='background-image:url(" + rel.cover.url + ");'></div></div>";
                        } else {
                            html += "<div class='border'><div class='icon' style='background-color:" + rel.cover.color + ";background-size: cover;'>" + rel.cover.font + "</div></div>";
                        }

                        html += "<span class='relate-title'><div class='topic-title'>" + rel.title + "</div></span></a></div>";
                    }
                }
            }
          
             $("#relate5").append(html);
             $('.loading').remove();
        $('.uc-addtop-btn').remove();
    }
    
    //相关文章
    function getRelateAt(data) {
            //改造新top5
        return '';
        if (data) {
            topJson.rel = data.list;
        } else {
            topJson.rel = [];
        }

        getFinish();
    }
    function getLocal(cityname) {
        //var url = "article_recommend.php?act=local&name="+cityname;
        //暂停本地文章
        return ;
        var url = makeUrlWithArg(CONFIG['article_recommend_url'], {act: "local", name: cityname});
        $.getJSON(url, function (data) {
            topJson.local = data.local;
            getFinish();
        })
    }

    function getRecommend() {
        //var url = "article_recommend.php";
        var url = makeUrlWithArg(CONFIG['article_recommend_url'], {act:"relate",pk:CONFIG['pkid'],app_id:CONFIG['app_id']});
        $.getJSON(url, function (data) {
            //改造新top5
//            topJson.top = data.top;
//            topJson.topic = data.topic;

            topJson.newtop = data.newtop;
            getFinish();
        });
    }

    function getDspRecommend(data) {
    
        if (data) {
            topJson.dsp = data.list;
    
            // 判断是否有位置信息
            if(data.ad_position){
                topJson.ad_position = data.ad_position;
            }
             
        } else {
            topJson.dsp = [];

        }
        getFinish();
    }

    function getFinish() {
        if (((topJson.top || topJson.topic) && topJson.local) || topJson.newtop) {
            //打乱数组
            if (topJson.rel && typeof (topJson.rel) != 'undefined') {
                topJson.rel.sort(function () {
                    return 0.5 - Math.random()
                });

            }


            if (topJson.topic && typeof (topJson.topic) != 'undefined') {
                var first = topJson.topic.shift();
                topJson.topic.sort(function () {
                    return 0.5 - Math.random()
                });
                topJson.topic.unshift(first);
            }

            if (topJson.top && typeof (topJson.top) != 'undefined') {
                topJson.top.sort(function () {
                    return 0.5 - Math.random()
                });

            }
            if (topJson.local && typeof (topJson.local) != 'undefined') {
                topJson.local.sort(function () {
                    return 0.5 - Math.random()
                });
            }

            if (topJson.picture && typeof (topJson.picture) != 'undefined') {
                if (topJson.picture[0] && topJson.picture[1]) {
                    var r0 = parseInt(topJson.picture[0].length * Math.random());
                    var r1 = parseInt(topJson.picture[1].length * Math.random());
                    topJson.picture[0] = topJson.picture[0][r0];
                    topJson.picture[1] = topJson.picture[1][r1];
                }
            }

            addHtml();
            if (!CONFIG['isTopBtn']) {
                $(window).scroll(function () {
                    if ($(document).height() - $(this).scrollTop() - document.body.clientHeight < 100) {
                        //addHtml();
                    }
                })
            } else {
                $(".uc-addtop-btn").click(function () {
                    addHtml();
                })
            }
        }
    }




   