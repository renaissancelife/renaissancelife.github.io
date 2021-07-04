var fix_video_size = function(vid,width,height){
				var ele1 = document.getElementById(vid);
		var qqvideoWidth = 0;
		if(document.defaultView && document.defaultView.getComputedStyle){
			qqvideoWidth = document.defaultView.getComputedStyle(ele1,null).width;
			qqvideoWidth = qqvideoWidth.replace('px','');
		}

		if(qqvideoWidth <= 0 || qqvideoWidth ==''){
			qqvideoWidth = document.body.clientWidth - 35;
		}

		var qqvideoHeight = qqvideoWidth * height / width ;

		var ele2 = document.getElementById('IFRAME_'+vid);
		ele1.style.width = qqvideoWidth + 'px';
		ele1.style.height = qqvideoHeight + 'px';
		if(ele2 == undefined) return ;
		var url = ele2.src;
		if(url.match(/yuntv\.letv\.com/g)){
			var reg_width    = /width=[\d]*/g;
			var reg_height   = /height=[\d]*[\.\d]*/g;
			var reg_auto_play = /auto_play=1/g;
			var parse_width  = 'width='+qqvideoWidth;
			var parse_height = 'height='+qqvideoHeight;
			var parse_auto_play = 'auto_play=0';

			if(url.match(reg_width)){
				url = url.replace(reg_width,parse_width)
			}else{
				url = url + '&' + parse_width;
			}
			if(url.match(reg_height)){
				url = url.replace(reg_height,parse_height)
			}else{
				url = url + '&' + parse_height;
			}
			if(url.match(reg_auto_play)){
				url = url.replace(reg_auto_play,parse_auto_play)
			}else{
				url = url + '&' + parse_auto_play;
			}

			ele2.src = url;
		}
		// ele1.style.paddingBottom = '0px';
		ele1.style.backgroundColor = '#fff';
		ele1.style.padding = '0px 0px';
    }