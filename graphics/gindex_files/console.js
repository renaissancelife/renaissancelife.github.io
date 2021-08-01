function consolejs(uiData){
    // console.log(uiData['site_opacity']);
    if(uiData){        
        // var tempUiData=uiData;
        let tempUiData1 = $.extend(true,[],uiData);
        var tempUiData=tempUiData1;
        uiDrawing(uiData);
    } 

    //默认预设
    var defaultUiDataBase={
        'search_opacity':'80',
        'search_width':'70',
        'search_height':'54',
        'search_radius':'4',

        'site_width':'100',
        'site_opacity':'80',
        'site_radius':'50',
        'siteli_size':'54',
        'site_row':'30',
        'site_column':'18',

        'main_width':'1000',
        'mask_opacity':'10',
        'search_spacing':'140',
    };

    //宽屏预设
    var defaultUiDataWide={
         'search_opacity':'80',
         'search_width':'70',
         'search_height':'66',
         'search_radius':'4',

         'site_width':'100',
         'site_opacity':'80',
         'site_radius':'50',
         'siteli_size':'68',
         'site_row':'40',
         'site_column':'28',

         'main_width':'1200',
         'mask_opacity':'10',
         'search_spacing':'200',
     };

    //极简预设
    var defaultUiDataSimple={
        'search_opacity':'20',
        'search_width':'40',
        'search_height':'50',
        'search_radius':'50',

        'site_width':'100',
        'site_opacity':'60',
        'site_radius':'50',
        'siteli_size':'38',
        'site_row':'0',
        'site_column':'5',

        'main_width':'1000',
        'mask_opacity':'40',
        'search_spacing':'110',
    };




    //ui panel
    $('.ui-adjust-btn').mousedown(function(){
        uiAdjust(this,this.dataset.paramsname,this.dataset.paramsmin,this.dataset.paramsmax,this.dataset.paramsunit);	
    });

    // $('.uiAdjustSearchWidth .uiAdjustBtn')[0].onmousedown=function(){
    //     uiAdjust(this,'searchWidth','20','100','%');	
    // }


    function uiAdjust(btnObj,paramsName,paramsMin,paramsMax,paramsUnit,e){
        var evt=e||event;
        var btnx=evt.clientX-btnObj.offsetLeft;	//按钮初始位置

        document.onmousemove=function(ev){			
            var event=ev||event;
            var moveLength=event.clientX-btnx;
            var adjustLength=btnObj.parentNode.offsetWidth-btnObj.offsetWidth;
            if(moveLength<=0){
                moveLength=0;
            }			
            else if(moveLength>=adjustLength){
                moveLength=adjustLength;
            }
            var paramObj=btnObj.parentNode.parentNode.getElementsByClassName('ui-params')[0];
            var params=parseInt(paramsMin)+parseInt(moveLength/adjustLength*(paramsMax-paramsMin));
            paramObj.innerHTML=params+paramsUnit;			
            btnObj.style.left=moveLength+'px';
            btnObj.parentNode.getElementsByClassName('ui-adjust-line')[0].style.width=moveLength+2+'px';
            tempUiData[paramsName]=params;
            //showConsoleBtn(tempUiData);
            uiDrawing(tempUiData);

        }

        document.onmouseup=function(){
            document.onmousemove=null;
        }
    }

    function initConsoleBtn(uiData){   
        for (let o in uiData) {
            for(var j=0;j<$('.ui-adjust-btn').length;j++){
                if($('.ui-adjust-btn')[j].dataset.paramsname==o){
                    var currentBtn=$('.ui-adjust-btn')[j];
                    var paramObj=currentBtn.parentNode.parentNode.getElementsByClassName('ui-params')[0];
                    paramObj.innerHTML=uiData[o]+currentBtn.dataset.paramsunit;	
                    var adjustLength=currentBtn.parentNode.offsetWidth-currentBtn.offsetWidth;
                    // console.log(adjustLength);
                    var leftNum=adjustLength*((parseInt(uiData[o])-parseInt(currentBtn.dataset.paramsmin))/(parseInt(currentBtn.dataset.paramsmax)-parseInt(currentBtn.dataset.paramsmin)));
                    currentBtn.style.left=leftNum+'px';
                    currentBtn.parentNode.getElementsByClassName('ui-adjust-line')[0].style.width=leftNum+2+'px';
                    //console.log(o);    //遍历的实际上是对象的属性名称 a,b,c
                }
            }
        }
    }
    
    


//uiDrawing *****************
    function uiDrawing(uiData){
        // console.log(uiData);
        $('#console-title-box')[0].style.display='none';
        $('#save-box')[0].style.display='flex';

        $('#search-wrap').css({
            'opacity':uiData['search_opacity']/100,
            "filter": "alpha(opacity="+uiData['search_opacity']+")",//搜索框透明度

            "width":uiData['search_width']+"%",//搜索框占比宽度

            "height":uiData['search_height']+"px",//搜索框高度值

            "border-radius":uiData['search_radius']+"px",//圆角大小

            "margin-bottom":uiData['search_spacing']+"px",//搜索框和网站栏间距
        
        });

        $('.site-wrap-li').css({
            "border-radius":uiData['site_radius']+"%",//圆角大小
            
            "width":uiData['siteli_size']+"px",
            "height":uiData['siteli_size']+"px",//li大小
        
        });

        $('.site-wrap-a').css({
            'opacity':uiData['site_opacity']/100,
            "filter": "alpha(opacity="+uiData['site_opacity']+")",//网站链接透明度

            "margin-left":Math.round(parseInt(uiData['site_row'])/2)+"px",
            "margin-right":Math.round(parseInt(uiData['site_row'])/2)+"px",

            "margin-top":Math.round(parseInt(uiData['site_column'])/2)+"px",
            "margin-bottom":Math.round(parseInt(uiData['site_column'])/2)+"px",
        
        });

        $('.site-title').css({            
            "width":parseInt(uiData['siteli_size'])+20+"px",//根据li大小调整最大显示宽度        
        });

        $('#site-box').css({            
            "width":uiData['site_width']+"%",//网站栏宽度占比   
        });

        $('#main-wrap').css({            
            "max-width":uiData['main_width']+"px",//总宽度   
        });

        $('#blackBg').css({            
            'opacity':uiData['mask_opacity']/100,
            "filter": "alpha(opacity="+uiData['mask_opacity']+")",//遮罩浓度
        });
        
    }



//点击眼睛图标（隐藏console框）*****************
    $('#console-eye-btn').mousedown(function(){
        $('#console-wrap').css({
            'opacity':0.03,
            "filter": "alpha(opacity=3)",
        });
    });

    $('#console-eye-btn').mouseup(function(){
        $('#console-wrap').css({
            'opacity':1,
            "filter": "alpha(opacity=100)",
        });
    });

    $('#console-eye-btn').mousemove(function(){
        $('#console-wrap').css({
            'opacity':1,
            "filter": "alpha(opacity=100)",
        });
    });
    

//标准预设 *****************
    $('.ui-default-btn').click(function(){
        let newUidata = $.extend(true,[],eval(this.dataset.defaultdata));
        tempUiData = newUidata;
        // console.log(tempUiData);
        uiDrawing(tempUiData);
        initConsoleBtn(tempUiData);
    });


    //小齿轮 点击 打开console wrap
    $('#console-btn').click(function(){        
        $("#fixedBtnBox").fadeOut(300);
        $("#beian-wrap").fadeOut(300);       
        
        $('#console-title-box')[0].style.display='flex';
        $('#save-box')[0].style.display='none';
        $('.console-title-img')[0].src='/static/home/images/console/jianfast.svg';        
        $('.console-title')[0].innerHTML='简法主页';
        $('#console-menu')[0].style.display='flex';        
        $('#console-content').hide();
        $('#console-wrap').fadeIn(300);
    });


    //背景设置按钮 点击
    $(".console-bgset-btn").click(function(){
        $('#console-wrap').fadeOut(300);
        $('#setBigBox').fadeIn(300);
    });


    //界面设置按钮 点击
    $('.console-uiset-btn')[0].onclick=function(){ 
        if(!this.dataset.usertype){
            showLoginBtn();
            return false;
        }
        $('#console-title-box')[0].style.display='flex';
        $('#save-box')[0].style.display='none';
        $('.console-title-img')[0].src='/static/home/images/console/set1.svg';        
        $('.console-title')[0].innerHTML='界面设置';                
        $('#console-menu').hide();        
        $('#console-content').show();
        initConsoleBtn(tempUiData);
        
    };

    //界面设置关闭按钮 点击
    $('#console-close-btn').click(function(){        
        $("#fixedBtnBox").fadeIn(300);       
        $('#console-wrap').fadeOut(300);
        uiDrawing(uiData);
    });


    //用户头像框 点击
    $('#user-box').click(function(){        
        if(!$('.console-menu-btn')[0].dataset.usertype){
            showLoginBtn();
        }
    });


    //保存ui界面设置按钮 点击
    $('#console-save-btn').click(function(){        
                //删除网站时Ajax至服务器
                var fd = new FormData();		//文件
                var req=new XMLHttpRequest();
                req.open('POST','/home/ui/saveui',true);//异步
                for (let o in tempUiData) {
                    fd.append(o,tempUiData[o]);
                }
                
                req.onreadystatechange=function(){
                    if(req.readyState==4 && req.status==200){					    	
                        if(JSON.parse(req.response)['error']){
                            showmsg(JSON.parse(req.response)['error']);						    	
                            return false;						    		
                        }
                        if(JSON.parse(req.response)['success']){
                            //关闭console wrap
                            $("#fixedBtnBox").fadeIn(300);       
                            $('#console-wrap').fadeOut(300);
                            // uiDrawing(JSON.parse(req.response));

                            showmsg(JSON.parse(req.response)['success']);
                            setTimeout(function(){
                                location.reload()
                            },2000);
                        }
                    }
                }
               
                req.send(fd);	
    });

    //弹出登录框，并且加来源后缀
    function showLoginBtn(){
        $('#loginBlackBg').fadeIn();
        function getQueryVariable(variable)
        {
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
                    var pair = vars[i].split("=");
                    if(pair[0] == variable){return pair[1];}
            }
            return(false);
        }

        if(getQueryVariable('urm_sour')){
            $('#loginBtnA')[0].href='/qqlogin/?time='+getQueryVariable('urm_sour');
        }
        if(getQueryVariable('page')){
            $('#loginBtnA')[0].href='/qqlogin/?time='+getQueryVariable('page');
        }
        if(getQueryVariable('engine')){
            $('#loginBtnA')[0].href='/qqlogin/?time='+getQueryVariable('engine');
        }
        if(getQueryVariable('time')){
            $('#loginBtnA')[0].href='/qqlogin/?time='+getQueryVariable('time');
        }
        return false;
    }

    //展示提示框showmsg--------------------------
    function showmsg(msgtext,showtime){
        var showtime=showtime||2000;
        //$('#msgbox').fadeIn(300);
        $('#msg-box').animate({opacity:"0.8",marginTop:"50px"},300);
        $('#msg-box')[0].style.display='inline-block';
        $('#msg-box')[0].innerHTML=msgtext;				

        setTimeout(function(){
            $('#msg-box').animate({opacity:"0"},300);
            setTimeout(function(){
                $('#msg-box')[0].style.display='none';
                $('#msg-box')[0].style.marginTop='20px';
                $('#msg-box')[0].innerHTML='';	
            },280);

        },showtime);
        
    }


}//consolejsend