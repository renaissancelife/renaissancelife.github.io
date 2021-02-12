// (c) Colin Barschel 2007-2008 - See shell.js for the original uncompressed version. termlib.js is (c) Norbert Landsteiner 2003-2007 http://www.masswerk.at

var Terminal=function(conf){if(typeof conf!='object')conf=new Object();else{for(var i in this.Defaults){if(typeof conf[i]=='undefined')conf[i]=this.Defaults[i];}}
this.conf=conf;this.setInitValues();}
Terminal.prototype={version:'1.43 (original)',Defaults:{cols:80,rows:24,x:100,y:100,termDiv:'termDiv',bgColor:'#181818',frameColor:'#555555',frameWidth:1,rowHeight:15,blinkDelay:500,fontClass:'term',crsrBlinkMode:false,crsrBlockMode:true,DELisBS:false,printTab:true,printEuro:true,catchCtrlH:true,closeOnESC:true,historyUnique:false,id:0,ps:'>',greeting:'%+r Terminal ready. %-r',handler:this.defaultHandler,ctrlHandler:null,initHandler:null,exitHandler:null,wrap:false},setInitValues:function(){this.isSafari=(navigator.userAgent.indexOf('Safari')>=0)?true:false;this.isOpera=(window.opera&&navigator.userAgent.indexOf('Opera')>=0)?true:false;this.id=this.conf.id;this.maxLines=this.conf.rows;this.maxCols=this.conf.cols;this.termDiv=this.conf.termDiv;this.crsrBlinkMode=this.conf.crsrBlinkMode;this.crsrBlockMode=this.conf.crsrBlockMode;this.blinkDelay=this.conf.blinkDelay;this.DELisBS=this.conf.DELisBS;this.printTab=this.conf.printTab;this.printEuro=this.conf.printEuro;this.catchCtrlH=this.conf.catchCtrlH;this.closeOnESC=this.conf.closeOnESC;this.historyUnique=this.conf.historyUnique;this.ps=this.conf.ps;this.closed=false;this.r;this.c;this.charBuf=new Array();this.styleBuf=new Array();this.scrollBuf=null;this.blinkBuffer=0;this.blinkTimer;this.cursoractive=false;this.lock=true;this.insert=false;this.charMode=false;this.rawMode=false;this.lineBuffer='';this.inputChar=0;this.lastLine='';this.guiCounter=0;this.history=new Array();this.histPtr=0;this.env=new Object();this.ns4ParentDoc=null;this.handler=this.conf.handler;this.wrapping=this.conf.wrapping;this.ctrlHandler=this.conf.ctrlHandler;this.initHandler=this.conf.initHandler;this.exitHandler=this.conf.exitHandler;},defaultHandler:function(){this.newLine();if(this.lineBuffer!=''){this.type('You typed: '+this.lineBuffer);this.newLine();}
this.prompt();},open:function(){if(this.termDivReady()){if(!this.closed)this._makeTerm();this.init();return true;}
else return false;},close:function(){this.lock=true;this.cursorOff();if(this.exitHandler)this.exitHandler();this.globals.setVisible(this.termDiv,0);this.closed=true;},init:function(){if(this.guiReady()){this.guiCounter=0;if(this.closed){this.setInitValues();}
this.clear();this.globals.setVisible(this.termDiv,1);this.globals.enableKeyboard(this);if(this.initHandler){this.initHandler();}
else{this.write(this.conf.greeting);this.newLine();this.prompt();}}
else{this.guiCounter++;if(this.guiCounter>18000){if(confirm('Terminal:\nYour browser hasn\'t responded for more than 2 minutes.\nRetry?'))this.guiCounter=0
else return;};this.globals.termToInitialze=this;window.setTimeout('Terminal.prototype.globals.termToInitialze.init()',200);}},getRowArray:function(l,v){var a=new Array();for(var i=0;i<l;i++)a[i]=v;return a;},wrapOn:function(){this.wrapping=true;},wrapOff:function(){this.wrapping=false;},type:function(text,style){for(var i=0;i<text.length;i++){var ch=text.charCodeAt(i);if(!this.isPrintable(ch))ch=94;this.charBuf[this.r][this.c]=ch;this.styleBuf[this.r][this.c]=(style)?style:0;var last_r=this.r;this._incCol();if(this.r!=last_r)this.redraw(last_r);}
this.redraw(this.r)},write:function(text,usemore){if(typeof text!='object'){if(typeof text!='string')text=''+text;if(text.indexOf('\n')>=0){var ta=text.split('\n');text=ta.join('%n');}}
else{if(text.join)text=text.join('%n')
else text=''+text;if(text.indexOf('\n')>=0){var ta=text.split('\n');text=ta.join('%n');}}
this._sbInit(usemore);var chunks=text.split('%');var esc=(text.charAt(0)!='%');var style=0;var styleMarkUp=this.globals.termStyleMarkup;for(var i=0;i<chunks.length;i++){if(esc){if(chunks[i].length>0)this._sbType(chunks[i],style)
else if(i>0)this._sbType('%',style);esc=false;}
else{var func=chunks[i].charAt(0);if((chunks[i].length==0)&&(i>0)){this._sbType("%",style);esc=true;}
else if(func=='n'){this._sbNewLine(true);if(chunks[i].length>1)this._sbType(chunks[i].substring(1),style);}
else if(func=='+'){var opt=chunks[i].charAt(1);opt=opt.toLowerCase();if(opt=='p')style=0
else if(styleMarkUp[opt])style|=styleMarkUp[opt];if(chunks[i].length>2)this._sbType(chunks[i].substring(2),style);}
else if(func=='-'){var opt=chunks[i].charAt(1);opt=opt.toLowerCase();if(opt=='p')style=0
else if(styleMarkUp[opt])style&=~styleMarkUp[opt];if(chunks[i].length>2)this._sbType(chunks[i].substring(2),style);}
else if((chunks[i].length>1)&&(func=='c')){var cinfo=this._parseColor(chunks[i].substring(1));style=(style&(~0xfffff0))|cinfo.style;if(cinfo.rest)this._sbType(cinfo.rest,style);}
else if((chunks[i].length>1)&&(chunks[i].charAt(0)=='C')&&(chunks[i].charAt(1)=='S')){this.clear();this._sbInit();if(chunks[i].length>2)this._sbType(chunks[i].substring(2),style);}
else{if(chunks[i].length>0)this._sbType(chunks[i],style);}}}
this._sbOut();},_parseColor:function(chunk){var rest='';var style=0;if(chunk.length){if(chunk.charAt(0)=='('){var clabel='';for(var i=1;i<chunk.length;i++){var c=chunk.charAt(i);if(c==')'){if(chunk.length>i)rest=chunk.substring(i+1);break;}
clabel+=c;}
if(clabel){if(clabel.charAt(0)=='@'){var sc=this.globals.nsColors[clabel.substring(1).toLowerCase()];if(sc)style=(16+sc)*0x100;}
else if(clabel.charAt(0)=='#'){var cl=clabel.substring(1).toLowerCase();var sc=this.globals.webColors[cl];if(sc){style=sc*0x10000;}
else{cl=this.globals.webifyColor(cl);if(cl)style=this.globals.webColors[cl]*0x10000;}}
else if((clabel.length)&&(clabel.length<=2)){var isHex=false;for(var i=0;i<clabel.length;i++){if(this.globals.isHexOnlyChar(clabel.charAt(i))){isHex=true;break;}}
var cl=(isHex)?parseInt(clabel,16):parseInt(clabel,10);if((!isNaN(cl))||(cl<=15)){style=cl*0x100;}}
else{style=this.globals.getColorCode(clabel)*0x100;}}}
else{var c=chunk.charAt(0);if(this.globals.isHexChar(c)){style=this.globals.hexToNum[c]*0x100;rest=chunk.substring(1);}
else{rest=chunk;}}}
return{rest:rest,style:style};},_sbInit:function(usemore){var sb=this.scrollBuf=new Object();var sbl=sb.lines=new Array();var sbs=sb.styles=new Array();sb.more=usemore;sb.line=0;sb.status=0;sb.r=0;sb.c=this.c;sbl[0]=this.getRowArray(this.conf.cols,0);sbs[0]=this.getRowArray(this.conf.cols,0);for(var i=0;i<this.c;i++){sbl[0][i]=this.charBuf[this.r][i];sbs[0][i]=this.styleBuf[this.r][i];}},_sbType:function(text,style){var sb=this.scrollBuf;for(var i=0;i<text.length;i++){var ch=text.charCodeAt(i);if(!this.isPrintable(ch))ch=94;sb.lines[sb.r][sb.c]=ch;sb.styles[sb.r][sb.c++]=(style)?style:0;if(sb.c>=this.maxCols)this._sbNewLine();}},_sbNewLine:function(forced){var sb=this.scrollBuf;if(this.wrapping&&forced){sb.lines[sb.r][sb.c]=10;sb.lines[sb.r].length=sb.c+1;}
sb.r++;sb.c=0;sb.lines[sb.r]=this.getRowArray(this.conf.cols,0);sb.styles[sb.r]=this.getRowArray(this.conf.cols,0);},_sbWrap:function(){var wb=new Object();wb.lines=new Array();wb.styles=new Array();wb.lines[0]=this.getRowArray(this.conf.cols,0);wb.styles[0]=this.getRowArray(this.conf.cols,0);wb.r=0;wb.c=0;var sb=this.scrollBuf;var sbl=sb.lines;var sbs=sb.styles;var ch,st,wrap,lc,ls;var l=this.c;var lastR=0;var lastC=0;wb.cBreak=false;for(var r=0;r<sbl.length;r++){lc=sbl[r];ls=sbs[r];for(var c=0;c<lc.length;c++){ch=lc[c];st=ls[c];if(ch){var wrap=this.globals.wrapChars[ch];if(ch==10)wrap=1;if(wrap){if(wrap==2){l++;}
else if(wrap==4){l++;lc[c]=45;}
this._wbOut(wb,lastR,lastC,l);if(ch==10){this._wbIncLine(wb);}
else if((wrap==1)&&(wb.c<this.maxCols)){wb.lines[wb.r][wb.c]=ch;wb.styles[wb.r][wb.c++]=st;if(wb.c>=this.maxCols)this._wbIncLine(wb);}
if(wrap==3){lastR=r;lastC=c;l=1;}
else{l=0;lastR=r;lastC=c+1;if(lastC==lc.length){lastR++;lastC=0;}
if(wrap==4)wb.cBreak=true;}}
else{l++;}}
else continue;}}
if(l){if((wb.cbreak)&&(wb.c!=0))wb.c--;this._wbOut(wb,lastR,lastC,l);}
sb.lines=wb.lines;sb.styles=wb.styles;sb.r=wb.r;sb.c=wb.c;},_wbOut:function(wb,br,bc,l){var sb=this.scrollBuf;var sbl=sb.lines;var sbs=sb.styles;var ofs=0;var lc,ls;if(l+wb.c>this.maxCols){if(l<this.maxCols){this._wbIncLine(wb);}
else{var i0=0;ofs=this.maxCols-wb.c;lc=sbl[br];ls=sbs[br];while(true){for(var i=i0;i<ofs;i++){wb.lines[wb.r][wb.c]=lc[bc];wb.styles[wb.r][wb.c++]=ls[bc++];if(bc==sbl[br].length){bc=0;br++;lc=sbl[br];ls=sbs[br];}}
this._wbIncLine(wb);if(l-ofs<this.maxCols)break;i0=ofs;ofs+=this.maxCols;}}}
else if(wb.cBreak){wb.c--;}
lc=sbl[br];ls=sbs[br];for(var i=ofs;i<l;i++){wb.lines[wb.r][wb.c]=lc[bc];wb.styles[wb.r][wb.c++]=ls[bc++];if(bc==sbl[br].length){bc=0;br++;lc=sbl[br];ls=sbs[br];}}
wb.cBreak=false;},_wbIncLine:function(wb){wb.r++;wb.c=0;wb.lines[wb.r]=this.getRowArray(this.conf.cols,0);wb.styles[wb.r]=this.getRowArray(this.conf.cols,0);},_sbOut:function(){var sb=this.scrollBuf;if((this.wrapping)&&(!sb.status))this._sbWrap();var sbl=sb.lines;var sbs=sb.styles;var tcb=this.charBuf;var tsb=this.styleBuf;var ml=this.maxLines;var buflen=sbl.length;if(sb.more){if(sb.status){if(this.inputChar==this.globals.lcMoreKeyAbort){this.r=ml-1;this.c=0;tcb[this.r]=this.getRowArray(this.conf.cols,0);tsb[this.r]=this.getRowArray(this.conf.cols,0);this.redraw(this.r);this.handler=sb.handler;this.charMode=false;this.inputChar=0;this.scrollBuf=null;this.prompt();return;}
else if(this.inputChar==this.globals.lcMoreKeyContinue){this.clear();}
else{return;}}
else{if(this.r>=ml-1)this.clear();}}
if(this.r+buflen-sb.line<=ml){for(var i=sb.line;i<buflen;i++){var r=this.r+i-sb.line;tcb[r]=sbl[i];tsb[r]=sbs[i];this.redraw(r);}
this.r+=sb.r-sb.line;this.c=sb.c;if(sb.more){if(sb.status)this.handler=sb.handler;this.charMode=false;this.inputChar=0;this.scrollBuf=null;this.prompt();return;}}
else if(sb.more){ml--;if(sb.status==0){sb.handler=this.handler;this.handler=this._sbOut;this.charMode=true;sb.status=1;}
if(this.r){var ofs=ml-this.r;for(var i=sb.line;i<ofs;i++){var r=this.r+i-sb.line;tcb[r]=sbl[i];tsb[r]=sbs[i];this.redraw(r);}}
else{var ofs=sb.line+ml;for(var i=sb.line;i<ofs;i++){var r=this.r+i-sb.line;tcb[r]=sbl[i];tsb[r]=sbs[i];this.redraw(r);}}
sb.line=ofs;this.r=ml;this.c=0;this.type(this.globals.lcMorePrompt1,this.globals.lcMorePromtp1Style);this.type(this.globals.lcMorePrompt2,this.globals.lcMorePrompt2Style);this.lock=false;return;}
else if(buflen>=ml){var ofs=buflen-ml;for(var i=0;i<ml;i++){var r=ofs+i;tcb[i]=sbl[r];tsb[i]=sbs[r];this.redraw(i);}
this.r=ml-1;this.c=sb.c;}
else{var dr=ml-buflen;var ofs=this.r-dr;for(var i=0;i<dr;i++){var r=ofs+i;for(var c=0;c<this.maxCols;c++){tcb[i][c]=tcb[r][c];tsb[i][c]=tsb[r][c];}
this.redraw(i);}
for(var i=0;i<buflen;i++){var r=dr+i;tcb[r]=sbl[i];tsb[r]=sbs[i];this.redraw(r);}
this.r=ml-1;this.c=sb.c;}
this.scrollBuf=null;},typeAt:function(r,c,text,style){var tr1=this.r;var tc1=this.c;this.cursorSet(r,c);for(var i=0;i<text.length;i++){var ch=text.charCodeAt(i);if(!this.isPrintable(ch))ch=94;this.charBuf[this.r][this.c]=ch;this.styleBuf[this.r][this.c]=(style)?style:0;var last_r=this.r;this._incCol();if(this.r!=last_r)this.redraw(last_r);}
this.redraw(this.r);this.r=tr1;this.c=tc1;},statusLine:function(text,style,offset){var ch,r;style=((style)&&(!isNaN(style)))?parseInt(style)&15:0;if((offset)&&(offset>0))r=this.conf.rows-offset
else r=this.conf.rows-1;for(var i=0;i<this.conf.cols;i++){if(i<text.length){ch=text.charCodeAt(i);if(!this.isPrintable(ch))ch=94;}
else ch=0;this.charBuf[r][i]=ch;this.styleBuf[r][i]=style;}
this.redraw(r);},printRowFromString:function(r,text,style){var ch;style=((style)&&(!isNaN(style)))?parseInt(style)&15:0;if((r>=0)&&(r<this.maxLines)){if(typeof text!='string')text=''+text;for(var i=0;i<this.conf.cols;i++){if(i<text.length){ch=text.charCodeAt(i);if(!this.isPrintable(ch))ch=94;}
else ch=0;this.charBuf[r][i]=ch;this.styleBuf[r][i]=style;}
this.redraw(r);}},setChar:function(ch,r,c,style){this.charBuf[r][c]=ch;this.styleBuf[this.r][this.c]=(style)?style:0;this.redraw(r);},newLine:function(){this.c=0;this._incRow();},_charOut:function(ch,style){this.charBuf[this.r][this.c]=ch;this.styleBuf[this.r][this.c]=(style)?style:0;this.redraw(this.r);this._incCol();},_incCol:function(){this.c++;if(this.c>=this.maxCols){this.c=0;this._incRow();}},_incRow:function(){this.r++;if(this.r>=this.maxLines){this._scrollLines(0,this.maxLines);this.r=this.maxLines-1;}},_scrollLines:function(start,end){window.status='Scrolling lines ...';start++;for(var ri=start;ri<end;ri++){var rt=ri-1;this.charBuf[rt]=this.charBuf[ri];this.styleBuf[rt]=this.styleBuf[ri];}
var rt=end-1;this.charBuf[rt]=this.getRowArray(this.conf.cols,0);this.styleBuf[rt]=this.getRowArray(this.conf.cols,0);this.redraw(rt);for(var r=end-1;r>=start;r--)this.redraw(r-1);window.status='';},clear:function(){window.status='Clearing display ...';this.cursorOff();this.insert=false;for(var ri=0;ri<this.maxLines;ri++){this.charBuf[ri]=this.getRowArray(this.conf.cols,0);this.styleBuf[ri]=this.getRowArray(this.conf.cols,0);this.redraw(ri);}
this.r=0;this.c=0;window.status='';},reset:function(){if(this.lock)return;this.lock=true;this.rawMode=false;this.charMode=false;this.maxLines=this.conf.rows;this.maxCols=this.conf.cols;this.lastLine='';this.lineBuffer='';this.inputChar=0;this.clear();},prompt:function(){this.lock=true;if(this.c>0)this.newLine();this.type(this.ps);this._charOut(1);this.lock=false;this.cursorOn();},isPrintable:function(ch,unicodePage1only){if((this.wrapping)&&(this.globals.wrapChars[ch]==4))return true;if((unicodePage1only)&&(ch>255)){return((ch==this.termKey.EURO)&&(this.printEuro))?true:false;}
return(((ch>=32)&&(ch!=this.termKey.DEL))||((this.printTab)&&(ch==this.termKey.TAB)));},cursorSet:function(r,c){var crsron=this.cursoractive;if(crsron)this.cursorOff();this.r=r%this.maxLines;this.c=c%this.maxCols;this._cursorReset(crsron);},cursorOn:function(){if(this.blinkTimer)clearTimeout(this.blinkTimer);this.blinkBuffer=this.styleBuf[this.r][this.c];this._cursorBlink();this.cursoractive=true;},cursorOff:function(){if(this.blinkTimer)clearTimeout(this.blinkTimer);if(this.cursoractive){this.styleBuf[this.r][this.c]=this.blinkBuffer;this.redraw(this.r);this.cursoractive=false;}},cursorLeft:function(){var crsron=this.cursoractive;if(crsron)this.cursorOff();var r=this.r;var c=this.c;if(c>0)c--
else if(r>0){c=this.maxCols-1;r--;}
if(this.isPrintable(this.charBuf[r][c])){this.r=r;this.c=c;}
this.insert=true;this._cursorReset(crsron);},cursorRight:function(){var crsron=this.cursoractive;if(crsron)this.cursorOff();var r=this.r;var c=this.c;if(c<this.maxCols-1)c++
else if(r<this.maxLines-1){c=0;r++;}
if(!this.isPrintable(this.charBuf[r][c])){this.insert=false;}
if(this.isPrintable(this.charBuf[this.r][this.c])){this.r=r;this.c=c;}
this._cursorReset(crsron);},backspace:function(){var crsron=this.cursoractive;if(crsron)this.cursorOff();var r=this.r;var c=this.c;if(c>0)c--
else if(r>0){c=this.maxCols-1;r--;};if(this.isPrintable(this.charBuf[r][c])){this._scrollLeft(r,c);this.r=r;this.c=c;};this._cursorReset(crsron);},fwdDelete:function(){var crsron=this.cursoractive;if(crsron)this.cursorOff();if(this.isPrintable(this.charBuf[this.r][this.c])){this._scrollLeft(this.r,this.c);if(!this.isPrintable(this.charBuf[this.r][this.c]))this.insert=false;}
this._cursorReset(crsron);},_cursorReset:function(crsron){if(crsron)this.cursorOn()
else{this.blinkBuffer=this.styleBuf[this.r][this.c];}},_cursorBlink:function(){if(this.blinkTimer)clearTimeout(this.blinkTimer);if(this==this.globals.activeTerm){if(this.crsrBlockMode){this.styleBuf[this.r][this.c]=(this.styleBuf[this.r][this.c]&1)?this.styleBuf[this.r][this.c]&254:this.styleBuf[this.r][this.c]|1;}
else{this.styleBuf[this.r][this.c]=(this.styleBuf[this.r][this.c]&2)?this.styleBuf[this.r][this.c]&253:this.styleBuf[this.r][this.c]|2;}
this.redraw(this.r);}
if(this.crsrBlinkMode)this.blinkTimer=setTimeout('Terminal.prototype.globals.activeTerm._cursorBlink()',this.blinkDelay);},_scrollLeft:function(r,c){var rows=new Array();rows[0]=r;while(this.isPrintable(this.charBuf[r][c])){var ri=r;var ci=c+1;if(ci==this.maxCols){if(ri<this.maxLines-1){ci=0;ri++;rows[rows.length]=ri;}
else{break;}}
this.charBuf[r][c]=this.charBuf[ri][ci];this.styleBuf[r][c]=this.styleBuf[ri][ci];c=ci;r=ri;}
if(this.charBuf[r][c]!=0)this.charBuf[r][c]=0;for(var i=0;i<rows.length;i++)this.redraw(rows[i]);},_scrollRight:function(r,c){var rows=new Array();var end=this._getLineEnd(r,c);var ri=end[0];var ci=end[1];if((ci==this.maxCols-1)&&(ri==this.maxLines-1)){if(r==0)return;this._scrollLines(0,this.maxLines);this.r--;r--;ri--;}
rows[r]=1;while(this.isPrintable(this.charBuf[ri][ci])){var rt=ri;var ct=ci+1;if(ct==this.maxCols){ct=0;rt++;rows[rt]=1;}
this.charBuf[rt][ct]=this.charBuf[ri][ci];this.styleBuf[rt][ct]=this.styleBuf[ri][ci];if((ri==r)&&(ci==c))break;ci--;if(ci<0){ci=this.maxCols-1;ri--;rows[ri]=1;}}
for(var i=r;i<this.maxLines;i++){if(rows[i])this.redraw(i);}},_getLineEnd:function(r,c){if(!this.isPrintable(this.charBuf[r][c])){c--;if(c<0){if(r>0){r--;c=this.maxCols-1;}
else{c=0;}}}
if(this.isPrintable(this.charBuf[r][c])){while(true){var ri=r;var ci=c+1;if(ci==this.maxCols){if(ri<this.maxLines-1){ri++;ci=0;}
else{break;}}
if(!this.isPrintable(this.charBuf[ri][ci]))break;c=ci;r=ri;}}
return[r,c];},_getLineStart:function(r,c){var ci,ri;if(!this.isPrintable(this.charBuf[r][c])){ci=c-1;ri=r;if(ci<0){if(ri==0)return[0,0];ci=this.maxCols-1;ri--;}
if(!this.isPrintable(this.charBuf[ri][ci]))return[r,c]
else{r=ri;c=ci;}}
while(true){var ri=r;var ci=c-1;if(ci<0){if(ri==0)break;ci=this.maxCols-1;ri--;}
if(!this.isPrintable(this.charBuf[ri][ci]))break;;r=ri;c=ci;}
return[r,c];},_getLine:function(){var end=this._getLineEnd(this.r,this.c);var r=end[0];var c=end[1];var line=new Array();while(this.isPrintable(this.charBuf[r][c])){line[line.length]=String.fromCharCode(this.charBuf[r][c]);if(c>0)c--
else if(r>0){c=this.maxCols-1;r--;}
else break;}
line.reverse();return line.join('');},_clearLine:function(){var end=this._getLineEnd(this.r,this.c);var r=end[0];var c=end[1];var line='';while(this.isPrintable(this.charBuf[r][c])){this.charBuf[r][c]=0;if(c>0){c--;}
else if(r>0){this.redraw(r);c=this.maxCols-1;r--;}
else break;}
if(r!=end[0])this.redraw(r);c++;this.cursorSet(r,c);this.insert=false;},focus:function(){this.globals.setFocus(this);},termKey:null,_makeTerm:function(rebuild){window.status='Building terminal ...';this.globals.hasLayers=(document.layers)?true:false;this.globals.hasSubDivs=(navigator.userAgent.indexOf('Gecko')<0);var divPrefix=this.termDiv+'_r';var s='';s+='<table border="0" cellspacing="0" cellpadding="'+this.conf.frameWidth+'">\n';s+='<tr><td bgcolor="'+this.conf.frameColor+'"><table border="0" cellspacing="0" cellpadding="2"><tr><td  bgcolor="'+this.conf.bgColor+'"><table border="0" cellspacing="0" cellpadding="0">\n';var rstr='';for(var c=0;c<this.conf.cols;c++)rstr+='&nbsp;';for(var r=0;r<this.conf.rows;r++){var termid=((this.globals.hasLayers)||(this.globals.hasSubDivs))?'':' id="'+divPrefix+r+'"';s+='<tr><td nowrap height="'+this.conf.rowHeight+'"'+termid+' class="'+this.conf.fontClass+'">'+rstr+'<\/td><\/tr>\n';}
s+='<\/table><\/td><\/tr>\n';s+='<\/table><\/td><\/tr>\n';s+='<\/table>\n';var termOffset=2+this.conf.frameWidth;if(this.globals.hasLayers){for(var r=0;r<this.conf.rows;r++){s+='<layer name="'+divPrefix+r+'" top="'+(termOffset+r*this.conf.rowHeight)+'" left="'+termOffset+'" class="'+this.conf.fontClass+'"><\/layer>\n';}
this.ns4ParentDoc=document.layers[this.termDiv].document;this.globals.termStringStart='<table border="0" cellspacing="0" cellpadding="0"><tr><td nowrap height="'+this.conf.rowHeight+'" class="'+this.conf.fontClass+'">';this.globals.termStringEnd='<\/td><\/tr><\/table>';}
else if(this.globals.hasSubDivs){for(var r=0;r<this.conf.rows;r++){s+='<div id="'+divPrefix+r+'" style="position:absolute; top:'+(termOffset+r*this.conf.rowHeight)+'px; left: '+termOffset+'px;" class="'+this.conf.fontClass+'"><\/div>\n';}
this.globals.termStringStart='<table border="0" cellspacing="0" cellpadding="0"><tr><td nowrap height="'+this.conf.rowHeight+'" class="'+this.conf.fontClass+'">';this.globals.termStringEnd='<\/td><\/tr><\/table>';}
this.globals.writeElement(this.termDiv,s);if(!rebuild){this.globals.setElementXY(this.termDiv,this.conf.x,this.conf.y);this.globals.setVisible(this.termDiv,1);}
window.status='';},rebuild:function(){var rl=this.conf.rows;var cl=this.conf.cols;for(var r=0;r<rl;r++){var cbr=this.charBuf[r];if(!cbr){this.charBuf[r]=this.getRowArray(cl,0);this.styleBuf[r]=this.getRowArray(cl,0);}
else if(cbr.length<cl){for(var c=cbr.length;c<cl;c++){this.charBuf[r][c]=0;this.styleBuf[r][c]=0;}}}
var resetcrsr=false;if(this.r>=rl){r=rl-1;resetcrsr=true;}
if(this.c>=cl){c=cl-1;resetcrsr=true;}
if((resetcrsr)&&(this.cursoractive))this.cursorOn();this._makeTerm(true);for(var r=0;r<rl;r++){this.redraw(r);}},moveTo:function(x,y){this.globals.setElementXY(this.termDiv,x,y);},resizeTo:function(x,y){if(this.termDivReady()){x=parseInt(x,10);y=parseInt(y,10);if((isNaN(x))||(isNaN(y))||(x<4)||(y<2))return false;this.maxCols=this.conf.cols=x;this.maxLines=this.conf.rows=y;this._makeTerm();this.clear();return true;}
else return false;},redraw:function(r){var s=this.globals.termStringStart;var curStyle=0;var tstls=this.globals.termStyles;var tscls=this.globals.termStyleClose;var tsopn=this.globals.termStyleOpen;var tspcl=this.globals.termSpecials;var tclrs=this.globals.colorCodes;var tnclrs=this.globals.nsColorCodes;var twclrs=this.globals.webColorCodes;var t_cb=this.charBuf;var t_sb=this.styleBuf;var clr;for(var i=0;i<this.conf.cols;i++){var c=t_cb[r][i];var cs=t_sb[r][i];if(cs!=curStyle){if(curStyle){if(curStyle&0xffff00)s+='</span>';for(var k=tstls.length-1;k>=0;k--){var st=tstls[k];if(curStyle&st)s+=tscls[st];}}
curStyle=cs;for(var k=0;k<tstls.length;k++){var st=tstls[k];if(curStyle&st)s+=tsopn[st];}
clr='';if(curStyle&0xff00){var cc=(curStyle&0xff00)>>>8;clr=(cc<16)?tclrs[cc]:'#'+tnclrs[cc-16];}
else if(curStyle&0xff0000){clr='#'+twclrs[(curStyle&0xff0000)>>>16];}
if(clr){if(curStyle&1){s+='<span style="background-color:'+clr+' !important;">';}
else{s+='<span style="color:'+clr+' !important;">';}}}
s+=(tspcl[c])?tspcl[c]:String.fromCharCode(c);}
if(curStyle>0){if(curStyle&0xffff00)s+='</span>';for(var k=tstls.length-1;k>=0;k--){var st=tstls[k];if(curStyle&st)s+=tscls[st];}}
s+=this.globals.termStringEnd;this.globals.writeElement(this.termDiv+'_r'+r,s,this.ns4ParentDoc);},guiReady:function(){ready=true;if(this.globals.guiElementsReady(this.termDiv,window.document)){for(var r=0;r<this.conf.rows;r++){if(this.globals.guiElementsReady(this.termDiv+'_r'+r,this.ns4ParentDoc)==false){ready=false;break;}}}
else ready=false;return ready;},termDivReady:function(){if(document.layers){return(document.layers[this.termDiv])?true:false;}
else if(document.getElementById){return(document.getElementById(this.termDiv))?true:false;}
else if(document.all){return(document.all[this.termDiv])?true:false;}
else{return false;}},getDimensions:function(){var w=0;var h=0;var d=this.termDiv;if(document.layers){if(document.layers[d]){w=document.layers[d].clip.right;h=document.layers[d].clip.bottom;}}
else if(document.getElementById){var obj=document.getElementById(d);if((obj)&&(obj.firstChild)){w=parseInt(obj.firstChild.offsetWidth,10);h=parseInt(obj.firstChild.offsetHeight,10);}
else if((obj)&&(obj.children)&&(obj.children[0])){w=parseInt(obj.children[0].offsetWidth,10);h=parseInt(obj.children[0].offsetHeight,10);}}
else if(document.all){var obj=document.all[d];if((obj)&&(obj.children)&&(obj.children[0])){w=parseInt(obj.children[0].offsetWidth,10);h=parseInt(obj.children[0].offsetHeight,10);}}
return{width:w,height:h};},globals:{termToInitialze:null,activeTerm:null,kbdEnabled:false,keylock:false,keyRepeatDelay1:450,keyRepeatDelay2:100,keyRepeatTimer:null,lcMorePrompt1:' -- MORE -- ',lcMorePromtp1Style:1,lcMorePrompt2:' (Type: space to continue, \'q\' to quit)',lcMorePrompt2Style:0,lcMoreKeyAbort:113,lcMoreKeyContinue:32,_initGlobals:function(){var tg=Terminal.prototype.globals;tg._extendMissingStringMethods();tg._initWebColors();tg._initDomKeyRef();Terminal.prototype.termKey=tg.termKey;},getHexChar:function(c){var tg=Terminal.prototype.globals;if(tg.isHexChar(c))return tg.hexToNum[c];return-1;},isHexChar:function(c){return(((c>='0')&&(c<='9'))||((c>='a')&&(c<='f'))||((c>='A')&&(c<='F')))?true:false;},isHexOnlyChar:function(c){return(((c>='a')&&(c<='f'))||((c>='A')&&(c<='F')))?true:false;},hexToNum:{'0':0,'1':1,'2':2,'3':3,'4':4,'5':5,'6':6,'7':7,'8':8,'9':9,'a':10,'b':11,'c':12,'d':13,'e':14,'f':15,'A':10,'B':11,'C':12,'D':13,'E':14,'F':15},webColors:[],webColorCodes:[''],colors:{black:1,red:2,green:3,yellow:4,blue:5,magenta:6,cyan:7,white:8,grey:9,red2:10,green2:11,yellow2:12,blue2:13,magenta2:14,cyan2:15,red1:2,green1:3,yellow1:4,blue1:5,magenta1:6,cyan1:7,gray:9,darkred:10,darkgreen:11,darkyellow:12,darkblue:13,darkmagenta:14,darkcyan:15,'default':0,clear:0},colorCodes:['','#000000','#ff0000','#00ff00','#ffff00','#0066ff','#ff00ff','#00ffff','#ffffff','#808080','#990000','#009900','#999900','#003399','#990099','#009999'],nsColors:{'aliceblue':1,'antiquewhite':2,'aqua':3,'aquamarine':4,'azure':5,'beige':6,'black':7,'blue':8,'blueviolet':9,'brown':10,'burlywood':11,'cadetblue':12,'chartreuse':13,'chocolate':14,'coral':15,'cornflowerblue':16,'cornsilk':17,'crimson':18,'darkblue':19,'darkcyan':20,'darkgoldenrod':21,'darkgray':22,'darkgreen':23,'darkkhaki':24,'darkmagenta':25,'darkolivegreen':26,'darkorange':27,'darkorchid':28,'darkred':29,'darksalmon':30,'darkseagreen':31,'darkslateblue':32,'darkslategray':33,'darkturquoise':34,'darkviolet':35,'deeppink':36,'deepskyblue':37,'dimgray':38,'dodgerblue':39,'firebrick':40,'floralwhite':41,'forestgreen':42,'fuchsia':43,'gainsboro':44,'ghostwhite':45,'gold':46,'goldenrod':47,'gray':48,'green':49,'greenyellow':50,'honeydew':51,'hotpink':52,'indianred':53,'indigo':54,'ivory':55,'khaki':56,'lavender':57,'lavenderblush':58,'lawngreen':59,'lemonchiffon':60,'lightblue':61,'lightcoral':62,'lightcyan':63,'lightgoldenrodyellow':64,'lightgreen':65,'lightgrey':66,'lightpink':67,'lightsalmon':68,'lightseagreen':69,'lightskyblue':70,'lightslategray':71,'lightsteelblue':72,'lightyellow':73,'lime':74,'limegreen':75,'linen':76,'maroon':77,'mediumaquamarine':78,'mediumblue':79,'mediumorchid':80,'mediumpurple':81,'mediumseagreen':82,'mediumslateblue':83,'mediumspringgreen':84,'mediumturquoise':85,'mediumvioletred':86,'midnightblue':87,'mintcream':88,'mistyrose':89,'moccasin':90,'navajowhite':91,'navy':92,'oldlace':93,'olive':94,'olivedrab':95,'orange':96,'orangered':97,'orchid':98,'palegoldenrod':99,'palegreen':100,'paleturquoise':101,'palevioletred':102,'papayawhip':103,'peachpuff':104,'peru':105,'pink':106,'plum':107,'powderblue':108,'purple':109,'red':110,'rosybrown':111,'royalblue':112,'saddlebrown':113,'salmon':114,'sandybrown':115,'seagreen':116,'seashell':117,'sienna':118,'silver':119,'skyblue':120,'slateblue':121,'slategray':122,'snow':123,'springgreen':124,'steelblue':125,'tan':126,'teal':127,'thistle':128,'tomato':129,'turquoise':130,'violet':131,'wheat':132,'white':133,'whitesmoke':134,'yellow':135,'yellowgreen':136},nsColorCodes:['','f0f8ff','faebd7','00ffff','7fffd4','f0ffff','f5f5dc','000000','0000ff','8a2be2','a52a2a','deb887','5f9ea0','7fff00','d2691e','ff7f50','6495ed','fff8dc','dc143c','00008b','008b8b','b8860b','a9a9a9','006400','bdb76b','8b008b','556b2f','ff8c00','9932cc','8b0000','e9967a','8fbc8f','483d8b','2f4f4f','00ced1','9400d3','ff1493','00bfff','696969','1e90ff','b22222','fffaf0','228b22','ff00ff','dcdcdc','f8f8ff','ffd700','daa520','808080','008000','adff2f','f0fff0','ff69b4','cd5c5c','4b0082','fffff0','f0e68c','e6e6fa','fff0f5','7cfc00','fffacd','add8e6','f08080','e0ffff','fafad2','90ee90','d3d3d3','ffb6c1','ffa07a','20b2aa','87cefa','778899','b0c4de','ffffe0','00ff00','32cd32','faf0e6','800000','66cdaa','0000cd','ba55d3','9370db','3cb371','7b68ee','00fa9a','48d1cc','c71585','191970','f5fffa','ffe4e1','ffe4b5','ffdead','000080','fdf5e6','808000','6b8e23','ffa500','ff4500','da70d6','eee8aa','98fb98','afeeee','db7093','ffefd5','ffdab9','cd853f','ffc0cb','dda0dd','b0e0e6','800080','ff0000','bc8f8f','4169e1','8b4513','fa8072','f4a460','2e8b57','fff5ee','a0522d','c0c0c0','87ceeb','6a5acd','708090','fffafa','00ff7f','4682b4','d2b48c','008080','d8bfd8','ff6347','40e0d0','ee82ee','f5deb3','ffffff','f5f5f5','ffff00','9acd32'],_webSwatchChars:['0','3','6','9','c','f'],_initWebColors:function(){var tg=Terminal.prototype.globals;var ws=tg._webColorSwatch;var wn=tg.webColors;var cc=tg.webColorCodes;var n=1;var a,b,c,al,bl,bs,cl;for(var i=0;i<6;i++){a=tg._webSwatchChars[i];al=a+a;for(var j=0;j<6;j++){b=tg._webSwatchChars[j];bl=al+b+b;bs=a+b;for(var k=0;k<6;k++){c=tg._webSwatchChars[k];cl=bl+c+c;wn[bs+c]=wn[cl]=n;cc[n]=cl;n++;}}}},webifyColor:function(s){var tg=Terminal.prototype.globals;if(s.length==6){var c='';for(var i=0;i<6;i+=2){var a=s.charAt(i);var b=s.charAt(i+1);if((tg.isHexChar(a))&&(tg.isHexChar(b))){c+=tg._webSwatchChars[Math.round(parseInt(a+b,16)/255*5)];}
else{return'';}}
return c;}
else if(s.length==3){var c='';for(var i=0;i<3;i++){var a=s.charAt(i);if(tg.isHexChar(a)){c+=tg._webSwatchChars[Math.round(parseInt(a,16)/15*5)];}
else{return'';}}
return c;}
else return'';},setColor:function(label,value){var tg=Terminal.prototype.globals;if((typeof label=='number')&&(label>=1)&&(label<=15)){tg.colorCodes[label]=value;}
else if(typeof label=='string'){label=label.toLowerCase();if((label.length==1)&&(tg.isHexChar(label))){var n=tg.hexToNum[label];if(n)tg.colorCodes[n]=value;}
else if(typeof tg.colors[label]!='undefined'){var n=tg.colors[label];if(n)tg.colorCodes[n]=value;}}},getColorString:function(label){var tg=Terminal.prototype.globals;if((typeof label=='number')&&(label>=0)&&(label<=15)){return tg.colorCodes[label];}
else if(typeof label=='string'){label=label.toLowerCase();if((label.length==1)&&(tg.isHexChar(label))){return tg.colorCodes[tg.hexToNum[label]];}
else if((typeof tg.colors[label]!='undefined')){return tg.colorCodes[tg.colors[label]];}}
return'';},getColorCode:function(label){var tg=Terminal.prototype.globals;if((typeof label=='number')&&(label>=0)&&(label<=15)){return label;}
else if(typeof label=='string'){label=label.toLowerCase();if((label.length==1)&&(tg.isHexChar(label))){return parseInt(label,16);}
else if((typeof tg.colors[label]!='undefined')){return tg.colors[label];}}
return 0;},insertText:function(text){var tg=Terminal.prototype.globals;var termRef=tg.activeTerm;if((!termRef)||(termRef.closed)||(tg.keylock)||(termRef.lock)||(termRef.charMode))return false;for(var i=0;i<text.length;i++){tg.keyHandler({which:text.charCodeAt(i),_remapped:true});}
return true;},importEachLine:function(text){var tg=Terminal.prototype.globals;var termRef=tg.activeTerm;if((!termRef)||(termRef.closed)||(tg.keylock)||(termRef.lock)||(termRef.charMode))return false;termRef.cursorOff();termRef._clearLine();text=text.replace(/\r\n?/g,'\n');var t=text.split('\n');for(var i=0;i<t.length;i++){for(var k=0;k<t[i].length;k++){tg.keyHandler({which:t[i].charCodeAt(k),_remapped:true});}
tg.keyHandler({which:term.termKey.CR,_remapped:true});}
return true;},importMultiLine:function(text){var tg=Terminal.prototype.globals;var termRef=tg.activeTerm;if((!termRef)||(termRef.closed)||(tg.keylock)||(termRef.lock)||(termRef.charMode))return false;termRef.lock=true;termRef.cursorOff();termRef._clearLine();text=text.replace(/\r\n?/g,'\n');var lines=text.split('\n');for(var i=0;i<lines.length;i++){termRef.type(lines[i]);if(i<lines.length-1)termRef.newLine();}
termRef.lineBuffer=text;termRef.lastLine='';termRef.inputChar=0;termRef.handler();return true;},normalize:function(n,m){var s=''+n;while(s.length<m)s='0'+s;return s;},fillLeft:function(t,n){if(typeof t!='string')t=''+t;while(t.length<n)t=' '+t;return t;},center:function(t,l){var s='';for(var i=t.length;i<l;i+=2)s+=' ';return s+t;},stringReplace:function(s1,s2,t){var l1=s1.length;var l2=s2.length;var ofs=t.indexOf(s1);while(ofs>=0){t=t.substring(0,ofs)+s2+t.substring(ofs+l1);ofs=t.indexOf(s1,ofs+l2);}
return t;},wrapChars:{9:1,10:1,12:4,13:1,32:1,40:3,45:2,61:2,91:3,94:3,123:3},setFocus:function(termref){Terminal.prototype.globals.activeTerm=termref;Terminal.prototype.globals.clearRepeatTimer();},termKey:{'NUL':0x00,'SOH':0x01,'STX':0x02,'ETX':0x03,'EOT':0x04,'ENQ':0x05,'ACK':0x06,'BEL':0x07,'BS':0x08,'BACKSPACE':0x08,'HT':0x09,'TAB':0x09,'LF':0x0A,'VT':0x0B,'FF':0x0C,'CR':0x0D,'SO':0x0E,'SI':0x0F,'DLE':0x10,'DC1':0x11,'DC2':0x12,'DC3':0x13,'DC4':0x14,'NAK':0x15,'SYN':0x16,'ETB':0x17,'CAN':0x18,'EM':0x19,'SUB':0x1A,'ESC':0x1B,'IS4':0x1C,'IS3':0x1D,'IS2':0x1E,'IS1':0x1F,'DEL':0x7F,'EURO':0x20AC,'LEFT':0x1C,'RIGHT':0x1D,'UP':0x1E,'DOWN':0x1F},termDomKeyRef:{},_domKeyMappingData:{'LEFT':'LEFT','RIGHT':'RIGHT','UP':'UP','DOWN':'DOWN','BACK_SPACE':'BS','RETURN':'CR','ENTER':'CR','ESCAPE':'ESC','DELETE':'DEL','TAB':'TAB'},_initDomKeyRef:function(){var tg=Terminal.prototype.globals;var m=tg._domKeyMappingData;var r=tg.termDomKeyRef;var k=tg.termKey;for(var i in m)r['DOM_VK_'+i]=k[m[i]];},registerEvent:function(obj,eventType,handler,capture){if(obj.addEventListener){obj.addEventListener(eventType.toLowerCase(),handler,capture);}
else{var et=eventType.toUpperCase();if((window.Event)&&(window.Event[et])&&(obj.captureEvents))obj.captureEvents(Event[et]);obj['on'+eventType.toLowerCase()]=handler;}},releaseEvent:function(obj,eventType,handler,capture){if(obj.removeEventListener){obj.removeEventListener(eventType.toLowerCase(),handler,capture);}
else{var et=eventType.toUpperCase();if((window.Event)&&(window.Event[et])&&(obj.releaseEvents))obj.releaseEvents(Event[et]);et='on'+eventType.toLowerCase();if((obj[et])&&(obj[et]==handler))obj.et=null;}},enableKeyboard:function(term){var tg=Terminal.prototype.globals;if(!tg.kbdEnabled){tg.registerEvent(document,'keypress',tg.keyHandler,true);tg.registerEvent(document,'keydown',tg.keyFix,true);tg.registerEvent(document,'keyup',tg.clearRepeatTimer,true);tg.kbdEnabled=true;}
tg.activeTerm=term;},disableKeyboard:function(term){var tg=Terminal.prototype.globals;if(tg.kbdEnabled){tg.releaseEvent(document,'keypress',tg.keyHandler,true);tg.releaseEvent(document,'keydown',tg.keyFix,true);tg.releaseEvent(document,'keyup',tg.clearRepeatTimer,true);tg.kbdEnabled=false;}
tg.activeTerm=null;},keyFix:function(e){var tg=Terminal.prototype.globals;var term=tg.activeTerm;if((tg.keylock)||(term.lock))return true;if(window.event){var ch=window.event.keyCode;if(!e)e=window.event;if(e.DOM_VK_UP){for(var i in tg.termDomKeyRef){if((e[i])&&(ch==e[i])){tg.keyHandler({which:tg.termDomKeyRef[i],_remapped:true,_repeat:(ch==0x1B)?true:false});if(e.preventDefault)e.preventDefault();if(e.stopPropagation)e.stopPropagation();e.cancleBubble=true;return false;}}
e.cancleBubble=false;return true;}
else{var termKey=term.termKey;var keyHandler=tg.keyHandler;if((ch==8)&&(!term.isSafari)&&(!term.isOpera))keyHandler({which:termKey.BS,_remapped:true,_repeat:true})
else if(ch==9)keyHandler({which:termKey.TAB,_remapped:true,_repeat:(term.printTab)?false:true})
else if(ch==37)keyHandler({which:termKey.LEFT,_remapped:true,_repeat:true})
else if(ch==39)keyHandler({which:termKey.RIGHT,_remapped:true,_repeat:true})
else if(ch==38)keyHandler({which:termKey.UP,_remapped:true,_repeat:true})
else if(ch==40)keyHandler({which:termKey.DOWN,_remapped:true,_repeat:true})
else if(ch==127)keyHandler({which:termKey.DEL,_remapped:true,_repeat:true})
else if((ch>=57373)&&(ch<=57376)){if(ch==57373)keyHandler({which:termKey.UP,_remapped:true,_repeat:true})
else if(ch==57374)keyHandler({which:termKey.DOWN,_remapped:true,_repeat:true})
else if(ch==57375)keyHandler({which:termKey.LEFT,_remapped:true,_repeat:true})
else if(ch==57376)keyHandler({which:termKey.RIGHT,_remapped:true,_repeat:true});}
else{e.cancleBubble=false;return true;}
if(e.preventDefault)e.preventDefault();if(e.stopPropagation)e.stopPropagation();e.cancleBubble=true;return false;}}},clearRepeatTimer:function(e){var tg=Terminal.prototype.globals;if(tg.keyRepeatTimer){clearTimeout(tg.keyRepeatTimer);tg.keyRepeatTimer=null;}},doKeyRepeat:function(ch){Terminal.prototype.globals.keyHandler({which:ch,_remapped:true,_repeated:true})},keyHandler:function(e){var tg=Terminal.prototype.globals;var term=tg.activeTerm;if((tg.keylock)||(term.lock))return true;if((window.event)&&(window.event.preventDefault))window.event.preventDefault()
else if((e)&&(e.preventDefault))e.preventDefault();if((window.event)&&(window.event.stopPropagation))window.event.stopPropagation()
else if((e)&&(e.stopPropagation))e.stopPropagation();var ch;var ctrl=false;var shft=false;var remapped=false;var termKey=term.termKey;var keyRepeat=0;if(e){ch=e.which;ctrl=(((e.ctrlKey)&&(e.altKey))||(e.modifiers==2));shft=((e.shiftKey)||(e.modifiers==4));if(e._remapped){remapped=true;if(window.event){ctrl=((ctrl)||((window.event.ctrlKey)&&(!window.event.altKey)));shft=((shft)||(window.event.shiftKey));}}
if(e._repeated){keyRepeat=2;}
else if(e._repeat){keyRepeat=1;}}
else if(window.event){ch=window.event.keyCode;ctrl=((window.event.ctrlKey)&&(!window.event.altKey));shft=(window.event.shiftKey);if(window.event._repeated){keyRepeat=2;}
else if(window.event._repeat){keyRepeat=1;}}
else{return true;}
if((ch=='')&&(remapped==false)){if(e==null)e=window.event;if((e.charCode==0)&&(e.keyCode)){if(e.DOM_VK_UP){var dkr=tg.termDomKeyRef;for(var i in dkr){if((e[i])&&(e.keyCode==e[i])){ch=dkr[i];break;}}}
else{if(e.keyCode==28)ch=termKey.LEFT
else if(e.keyCode==29)ch=termKey.RIGHT
else if(e.keyCode==30)ch=termKey.UP
else if(e.keyCode==31)ch=termKey.DOWN
else if(e.keyCode==37)ch=termKey.LEFT
else if(e.keyCode==39)ch=termKey.RIGHT
else if(e.keyCode==38)ch=termKey.UP
else if(e.keyCode==40)ch=termKey.DOWN
else if(e.keyCode==9)ch=termKey.TAB;}}}
if((ch>=0xE000)&&(ch<=0xF8FF))return;if(keyRepeat){tg.clearRepeatTimer();tg.keyRepeatTimer=window.setTimeout('Terminal.prototype.globals.doKeyRepeat('+ch+')',(keyRepeat==1)?tg.keyRepeatDelay1:tg.keyRepeatDelay2);}
if(term.charMode){term.insert=false;term.inputChar=ch;term.lineBuffer='';term.handler();if((ch<=32)&&(window.event))window.event.cancleBubble=true;return false;}
if(!ctrl){if(ch==termKey.CR){term.lock=true;term.cursorOff();term.insert=false;if(term.rawMode){term.lineBuffer=term.lastLine;}
else{term.lineBuffer=term._getLine();if((term.lineBuffer!='')&&((!term.historyUnique)||(term.history.length==0)||(term.lineBuffer!=term.history[term.history.length-1]))){term.history[term.history.length]=term.lineBuffer;}
term.histPtr=term.history.length;}
term.lastLine='';term.inputChar=0;term.handler();if(window.event)window.event.cancleBubble=true;return false;}
else if(ch==termKey.ESC&&term.conf.closeOnESC){term.close();if(window.event)window.event.cancleBubble=true;return false;}
if((ch<32)&&(term.rawMode)){if(window.event)window.event.cancleBubble=true;return false;}
else{if(ch==termKey.LEFT){term.cursorLeft();if(window.event)window.event.cancleBubble=true;return false;}
else if(ch==termKey.RIGHT){term.cursorRight();if(window.event)window.event.cancleBubble=true;return false;}
else if(ch==termKey.UP){term.cursorOff();if(term.histPtr==term.history.length)term.lastLine=term._getLine();term._clearLine();if((term.history.length)&&(term.histPtr>=0)){if(term.histPtr>0)term.histPtr--;term.type(term.history[term.histPtr]);}
else if(term.lastLine)term.type(term.lastLine);term.cursorOn();if(window.event)window.event.cancleBubble=true;return false;}
else if(ch==termKey.DOWN){term.cursorOff();if(term.histPtr==term.history.length)term.lastLine=term._getLine();term._clearLine();if((term.history.length)&&(term.histPtr<=term.history.length)){if(term.histPtr<term.history.length)term.histPtr++;if(term.histPtr<term.history.length)term.type(term.history[term.histPtr])
else if(term.lastLine)term.type(term.lastLine);}
else if(term.lastLine)term.type(term.lastLine);term.cursorOn();if(window.event)window.event.cancleBubble=true;return false;}
else if(ch==termKey.BS){term.backspace();if(window.event)window.event.cancleBubble=true;return false;}
else if(ch==termKey.DEL){if(term.DELisBS)term.backspace()
else term.fwdDelete();if(window.event)window.event.cancleBubble=true;return false;}}}
if(term.rawMode){if(term.isPrintable(ch)){term.lastLine+=String.fromCharCode(ch);}
if((ch==32)&&(window.event))window.event.cancleBubble=true
else if((window.opera)&&(window.event))window.event.cancleBubble=true;return false;}
else{if((term.conf.catchCtrlH)&&((ch==termKey.BS)||((ctrl)&&(ch==72)))){term.backspace();if(window.event)window.event.cancleBubble=true;return false;}
else if((term.ctrlHandler)&&((ch<32)||((ctrl)&&(term.isPrintable(ch,true))))){if(((ch>=65)&&(ch<=96))||(ch==63)){if(ch==63)ch=31
else ch-=64;}
term.inputChar=ch;term.ctrlHandler();if(window.event)window.event.cancleBubble=true;return false;}
else if((ctrl)||(!term.isPrintable(ch,true))){if(window.event)window.event.cancleBubble=true;return false;}
else if(term.isPrintable(ch,true)){if(term.blinkTimer)clearTimeout(term.blinkTimer);if(term.insert){term.cursorOff();term._scrollRight(term.r,term.c);}
term._charOut(ch);term.cursorOn();if((ch==32)&&(window.event))window.event.cancleBubble=true
else if((window.opera)&&(window.event))window.event.cancleBubble=true;return false;}}
return true;},hasSubDivs:false,hasLayers:false,termStringStart:'',termStringEnd:'',termSpecials:{0:'&nbsp;',1:'&nbsp;',9:'&nbsp;',32:'&nbsp;',34:'&quot;',38:'&amp;',60:'&lt;',62:'&gt;',127:'&loz;',0x20AC:'&euro;'},termStyles:[1,2,4,8],termStyleMarkup:{'r':1,'u':2,'i':4,'s':8},termStyleOpen:{1:'<span class="termReverse">',2:'<u>',4:'<i>',8:'<strike>'},termStyleClose:{1:'<\/span>',2:'<\/u>',4:'<\/i>',8:'<\/strike>'},assignStyle:function(styleCode,markup,htmlOpen,htmlClose){var tg=Terminal.prototype.globals;if((!styleCode)||(isNaN(styleCode))){if(styleCode>=256){alert('termlib.js:\nCould not assign style.\n'+s+' is not a valid power of 2 between 0 and 256.');return;}}
var s=styleCode&0xff;var matched=false;for(var i=0;i<8;i++){if((s>>>i)&1){if(matched){alert('termlib.js:\nCould not assign style code.\n'+s+' is not a power of 2!');return;}
matched=true;}}
if(!matched){alert('termlib.js:\nCould not assign style code.\n'+s+' is not a valid power of 2 between 0 and 256.');return;}
markup=String(markup).toLowerCase();if((markup=='c')||(markup=='p')){alert('termlib.js:\nCould not assign mark up.\n"'+markup+'" is a reserved code.');return;}
if(markup.length>1){alert('termlib.js:\nCould not assign mark up.\n"'+markup+'" is not a single letter code.');return;}
var exists=false;for(var i=0;i<tg.termStyles.length;i++){if(tg.termStyles[i]==s){exists=true;break;}}
if(exists){var m=tg.termStyleMarkup[markup];if((m)&&(m!=s)){alert('termlib.js:\nCould not assign mark up.\n"'+markup+'" is already in use.');return;}}
else{if(tg.termStyleMarkup[markup]){alert('termlib.js:\nCould not assign mark up.\n"'+markup+'" is already in use.');return;}
tg.termStyles[tg.termStyles.length]=s;}
tg.termStyleMarkup[markup]=s;tg.termStyleOpen[s]=htmlOpen;tg.termStyleClose[s]=htmlClose;},writeElement:function(e,t,d){if(document.layers){var doc=(d)?d:window.document;doc.layers[e].document.open();doc.layers[e].document.write(t);doc.layers[e].document.close();}
else if(document.getElementById){var obj=document.getElementById(e);obj.innerHTML=t;}
else if(document.all){document.all[e].innerHTML=t;}},setElementXY:function(d,x,y){if(document.layers){document.layers[d].moveTo(x,y);}
else if(document.getElementById){var obj=document.getElementById(d);obj.style.left=x+'px';obj.style.top=y+'px';}
else if(document.all){document.all[d].style.left=x+'px';document.all[d].style.top=y+'px';}},setVisible:function(d,v){if(document.layers){document.layers[d].visibility=(v)?'show':'hide';}
else if(document.getElementById){var obj=document.getElementById(d);obj.style.visibility=(v)?'visible':'hidden';}
else if(document.all){document.all[d].style.visibility=(v)?'visible':'hidden';}},setDisplay:function(d,v){if(document.getElementById){var obj=document.getElementById(d);obj.style.display=v;}
else if(document.all){document.all[d].style.display=v;}},guiElementsReady:function(e,d){if(document.layers){var doc=(d)?d:window.document;return((doc)&&(doc.layers[e]))?true:false;}
else if(document.getElementById){return(document.getElementById(e))?true:false;}
else if(document.all){return(document.all[e])?true:false;}
else return false;},_termString_makeKeyref:function(){var tg=Terminal.prototype.globals;var termString_keyref=tg.termString_keyref=new Array();var termString_keycoderef=tg.termString_keycoderef=new Array();var hex=new Array('A','B','C','D','E','F');for(var i=0;i<=15;i++){var high=(i<10)?i:hex[i-10];for(var k=0;k<=15;k++){var low=(k<10)?k:hex[k-10];var cc=i*16+k;if(cc>=32){var cs=unescape("%"+high+low);termString_keyref[cc]=cs;termString_keycoderef[cs]=cc;}}}},_extendMissingStringMethods:function(){if((!String.fromCharCode)||(!String.prototype.charCodeAt)){Terminal.prototype.globals._termString_makeKeyref();}
if(!String.fromCharCode){String.fromCharCode=function(cc){return(cc!=null)?Terminal.prototype.globals.termString_keyref[cc]:'';};}
if(!String.prototype.charCodeAt){String.prototype.charCodeAt=function(n){cs=this.charAt(n);return(Terminal.prototype.globals.termString_keycoderef[cs])?Terminal.prototype.globals.termString_keycoderef[cs]:0;};}}}}
Terminal.prototype.globals._initGlobals();var TerminalDefaults=Terminal.prototype.Defaults;var termDefaultHandler=Terminal.prototype.defaultHandler;var TermGlobals=Terminal.prototype.globals;var termKey=Terminal.prototype.globals.termKey;var termDomKeyRef=Terminal.prototype.globals.termDomKeyRef;var parserWhiteSpace={' ':true,'\t':true}
var parserQuoteChars={'"':true,"'":true,'`':true};var parserSingleEscapes={'\\':true};var parserOptionChars={'-':true}
var parserEscapeExpressions={'%':parserHexExpression}
function parserHexExpression(termref,pointer,echar,quotelevel){if(termref.lineBuffer.length>pointer+2){var hi=termref.lineBuffer.charAt(pointer+1);var lo=termref.lineBuffer.charAt(pointer+2);lo=lo.toUpperCase();hi=hi.toUpperCase();if((((hi>='0')&&(hi<='9'))||((hi>='A')&&((hi<='F'))))&&(((lo>='0')&&(lo<='9'))||((lo>='A')&&((lo<='F'))))){parserEscExprStrip(termref,pointer+1,pointer+3);return String.fromCharCode(parseInt(hi+lo,16));}}
return echar;}
function parserEscExprStrip(termref,from,to){termref.lineBuffer=termref.lineBuffer.substring(0,from)+
termref.lineBuffer.substring(to);}
function parserGetopt(termref,optsstring){var opts={'illegals':[]};while((termref.argc<termref.argv.length)&&(termref.argQL[termref.argc]=='')){var a=termref.argv[termref.argc];if((a.length>0)&&(parserOptionChars[a.charAt(0)])){var i=1;while(i<a.length){var c=a.charAt(i);var v='';while(i<a.length-1){var nc=a.charAt(i+1);if((nc=='.')||((nc>='0')&&(nc<='9'))){v+=nc;i++;}
else break;}
if(optsstring.indexOf(c)>=0){opts[c]=(v=='')?{value:-1}:(isNaN(v))?{value:0}:{value:parseFloat(v)};}
else{opts.illegals[opts.illegals.length]=c;}
i++;}
termref.argc++;}
else break;}
return opts;}
function parseLine(termref){var argv=[''];var argQL=[''];var argc=0;var escape=false;for(var i=0;i<termref.lineBuffer.length;i++){var ch=termref.lineBuffer.charAt(i);if(escape){argv[argc]+=ch;escape=false;}
else if(parserEscapeExpressions[ch]){var v=parserEscapeExpressions[ch](termref,i,ch,argQL[argc]);if(typeof v!='undefined')argv[argc]+=v;}
else if(parserQuoteChars[ch]){if(argQL[argc]){if(argQL[argc]==ch){argc++;argv[argc]=argQL[argc]='';}
else{argv[argc]+=ch;}}
else{if(argv[argc]!=''){argc++;argv[argc]='';argQL[argc]=ch;}
else{argQL[argc]=ch;}}}
else if(parserWhiteSpace[ch]){if(argQL[argc]){argv[argc]+=ch;}
else if(argv[argc]!=''){argc++;argv[argc]=argQL[argc]='';}}
else if(parserSingleEscapes[ch]){escape=true;}
else{argv[argc]+=ch;}}
if((argv[argc]=='')&&(!argQL[argc])){argv.length--;argQL.length--;}
termref.argv=argv;termref.argQL=argQL;termref.argc=0;}
TermlibInvaders={version:'1.1 (original)',start:function(termref,maxcols,maxrows){if(!Terminal||!termref||parseFloat(termref.version)<1.4){return false;}
if(termref.conf.cols<68||termref.conf.rows<20){return false;}
var gc=TermlibInvaders.getStyleColorFromHexString;termref.env.invaders={termref:termref,maxCols:maxcols||0,maxRows:maxrows||0,charMode:termref.charMode,paused:false,moveAll:true,rows:3,cols:5,maxBombs:3,bombRate:0.005,timer:null,delay:50,newWaveDelay:1500,textColor:gc(TermlibInvaders.textColor),invaderColor:gc(TermlibInvaders.invaderColor),invaderHitColor:gc(TermlibInvaders.invaderHitColor),bombColor:gc(TermlibInvaders.bombColor),blockColor:gc(TermlibInvaders.blockColor),statusColor:gc(TermlibInvaders.statusColor),shotColor:gc(TermlibInvaders.shotColor),shipColor:gc(TermlibInvaders.shipColor),shipHitColor:gc(TermlibInvaders.shipHitColor),alertColor:gc(TermlibInvaders.alertColor),frameColor:gc(TermlibInvaders.frameColor)};TermlibInvaders.init.apply(termref);return true;},textColor:'#00cc00',invaderColor:'#00cc00',invaderHitColor:'#66aa66',bombColor:'#cccc00',blockColor:'#bbbb00',statusColor:'#00bb00',shotColor:'#aacc00',shipColor:'#aacc00',shipHitColor:'#aaaaaa',alertColor:'#ff9900',frameChar:'*',frameColor:'#777777',sprites:['       ',' (^o^) ',' (^-^) ','  ( ) ',' (   )',' (=^=) ',' ((.)) ',' ( . ) ','( (.) )',' ( . ) ','(  .  )','   .   ','       '],splashScreen:['%c(#0c0)%+i** T E R M L I B - I N V A D E R S **%-i','','','%c(#0c0)Instructions:','','%c(#0c0)  use cursor LEFT and RIGHT to move','%c(#0c0)  (or use vi movements alternatively)','%c(#0c0)  press space to fire','%c(#0c0)','%c(#0c0)  press "q" or "esc" to quit,','%c(#0c0)  "p" to pause the game.','','','%c(#0c0)%+r   press any key to start the game   %-r','','','%c(#0c0)(c) mass:werk N.Landsteiner 2005-2008','%c(#0c0)based on JS/UIX-Invaders by mass:werk'],splashScreenWidth:40,gameOverScreen:['                          ','%c(#f90)    G A M E  O V E R !    ','                          ','%c(#0c0) press any key to restart,','%c(#0c0) "q" or "esc" for quit.   ','                          '],gameOverScreenWidth:26,invObject:function(y,x){this.x=x;this.y=y;this.status=1;},init:function(){var inv=this.env.invaders;inv.termHandler=this.handler;if(this.maxLines!=this.conf.rows){inv.charBuf=new Array();inv.styleBuf=new Array();for(var r=this.conf.rows-1;r>=this.maxLines;r--){var cb=new Array();var sb=new Array();var tcb=this.charBuf[r];var tsb=this.styleBuf[r];for(var c=0;c=this.conf.cols;c++){cb[c]=tcb[c];sb[c]=tsb[c];}
inv.charBuf.push(cb);inv.styleBuf.push(sb);}
this.maxLines=this.conf.rows;}
if(this.maxCols!=this.conf.cols){inv.termMaxCols=this.maxCols;this.maxCols=this.conf.cols;}
else{inv.termMaxCols=-1;}
inv.keyRepeatDelay1=this.keyRepeatDelay1;inv.keyRepeatDelay2=this.keyRepeatDelay2;this.keyRepeatDelay1=this.keyRepeatDelay2=inv.delay-1;this.clear();TermlibInvaders.writeToCenter.apply(this,[TermlibInvaders.splashScreen,TermlibInvaders.splashScreenWidth]);this.charMode=true;this.lock=false;this.handler=TermlibInvaders.splashScreenHandler;},splashScreenHandler:function(){var key=this.inputChar;if(key==this.termKey.ESC||key==113){TermlibInvaders.exit.apply(this);return;}
var inv=this.env.invaders;TermlibInvaders.buildScreen.apply(this);inv.maxRight=inv.width-7;inv.wave=0;inv.score=0;var d=Math.floor(inv.width/5);var d1=Math.floor((inv.width-3*d)/2);inv.blockpos=new Array();for(var i=0;i<4;i++){var x=d1+i*d;inv.blockpos.push(x-1);inv.blockpos.push(x);inv.blockpos.push(x+1);}
TermlibInvaders.newWave.apply(this);},newWave:function(){this.clear();var inv=this.env.invaders;inv.wave++;var s='W A V E  # '+inv.wave;var c=Math.floor((this.conf.cols-s.length)/2);var r=Math.floor((this.conf.rows-3)/2)-4;this.typeAt(r,c,s,4|inv.textColor);this.typeAt(r+2,c,'Get ready ...',inv.textColor);inv.timer=setTimeout(function(){TermlibInvaders.waveStart.apply(inv.termref);},inv.newWaveDelay);this.lock=true;},waveStart:function(){var inv=this.env.invaders;clearTimeout(inv.timer);this.clear();TermlibInvaders.drawFrame.apply(this);inv.smove=0;inv.phase=1;inv.dir=1;inv.population=0;inv.shot=inv.shotX=0
inv.over=false;inv.bombs=0;inv.invrows=(inv.wave==2)?inv.rows+1:inv.rows;inv.invcols=(inv.wave<=2)?inv.cols:inv.cols+1;var changed=inv.changed=new Array();inv.inv=new Array();for(var r=0;r<inv.invrows;r++){var ir=inv.inv[r]=new Array();for(var c=0;c<inv.invcols;c++){ir[c]=new TermlibInvaders.invObject(r*2+1,c*8);inv.population++;}}
inv.block=this.getRowArray(inv.width,false);for(var i=0;i<inv.blockpos.length;i++){var x=inv.blockpos[i];inv.block[x]=true;TermlibInvaders.drawSprite(this,inv.blockY,x,'H',inv.blockColor);}
inv.bomb=new Array();inv.shipX=inv.shipCenter;TermlibInvaders.drawScoreBG.apply(this);TermlibInvaders.displayScore.apply(this);TermlibInvaders.drawSprite(this,inv.shipY,inv.shipX,TermlibInvaders.sprites[5],inv.shipColor);for(var i=0;i<this.maxLines;i++){this.redraw(i);changed[i]=false;}
inv.moveAll=true;TermlibInvaders.invStep(inv);inv.timer=setTimeout(function(){TermlibInvaders.mainLoop.apply(inv.termref);},inv.delay);this.lock=false;this.handler=TermlibInvaders.keyHandler;},mainLoop:function(){var inv=this.env.invaders;clearTimeout(inv.timer);var now=new Date();var enterTime=now.getTime();if(inv.smove){inv.shipX+=inv.smove;inv.smove=0;TermlibInvaders.drawSprite(this,inv.shipY,inv.shipX,TermlibInvaders.sprites[5],inv.shipColor);}
var s=inv.sore;TermlibInvaders.invStep(inv);var changed=inv.changed;for(var i=0;i<this.maxLines;i++){if(changed[i]){this.redraw(i);changed[i]=false;}}
inv.moveAll=!inv.moveAll;if(s!=inv.score)TermlibInvaders.displayScore.apply(this);if(inv.population==0){this.lock=true;inv.phase=-1;inv.timer=setTimeout(function(){TermlibInvaders.waveEnd.apply(inv.termref);},inv.delay*2);}
else if(inv.invbottom==inv.shipY||inv.over){this.lock=true;inv.phase=(inv.over)?6:5;TermlibInvaders.gameOver.apply(this);}
else{now=new Date();var delay=Math.max(1,inv.delay-(now.getTime()-enterTime));inv.timer=setTimeout(function(){TermlibInvaders.mainLoop.apply(inv.termref);},delay);}},invStep:function(inv){var term=inv.termref;var br=0,bl=inv.right,bb=0,dir=inv.dir;var linestep=((inv.invleft==0)||(inv.invright==inv.maxRight));var shot=(inv.shot>0),shotx=inv.shotX,shoty=inv.shipY-inv.shot;var bomb=inv.bomb,block=inv.block,blocky=inv.blockY,isblockrow=false;var sprites=TermlibInvaders.sprites,invclr=inv.invaderColor;var moveAll=inv.moveAll;if(shot&&inv.shot>1)TermlibInvaders.drawSprite(term,shoty+1,shotx,' ',0);for(var r=0;r<inv.invrows;r++){var ir=inv.inv[r];for(var c=0;c<inv.invcols;c++){var i=ir[c];if(i.status==1){if(moveAll&&linestep){TermlibInvaders.drawSprite(term,i.y,i.x,sprites[0],invclr);i.y++;}
if(shot&&shoty==i.y&&shotx>i.x&&shotx<(i.x+6)){i.status=2;inv.population--;inv.score+=50;inv.shot=shot=0;TermlibInvaders.drawSprite(term,i.y,i.x,sprites[3],inv.invaderHitColor);}
else if(moveAll){TermlibInvaders.drawSprite(term,i.y,i.x,sprites[inv.phase],invclr);if(i.y<inv.bombMaxY&&inv.bombs<inv.maxBombs&&Math.random()<inv.bombRate){for(var n=0;n<inv.maxBombs;n++){if(bomb[n]==null){bomb[n]=new TermlibInvaders.invObject(i.y+1,i.x+3);inv.bombs++;break;}}}
if(i.y==blocky)isblockrow=true;bb=Math.max(i.y,bb);}
else{i.x+=dir;br=Math.max(i.x,br);bl=Math.min(i.x,bl);}}
else if(i.status==2){TermlibInvaders.drawSprite(term,i.y,i.x,sprites[4],inv.invaderHitColor);i.status=3}
else if(i.status==3){TermlibInvaders.drawSprite(term,i.y,i.x,sprites[0],invclr);i.status=0;}}}
for(var n=0;n<inv.maxBombs;n++){var b=bomb[n];if(b!=null){var _br=inv.top+b.y-1;var _bc=inv.left+b.x;if(term.charBuf[_br][_bc]==86)TermlibInvaders.drawSprite(term,b.y-1,b.x,' ',0);if(b.y==blocky&&block[b.x]){block[b.x]=false;TermlibInvaders.drawSprite(term,blocky,b.x,' ',0);b=bomb[n]=null;inv.bombs--;}
else if(b.y==inv.shipY){if(b.x>inv.shipX&&b.x<(inv.shipX+6)){inv.over=true;}
else{b=bomb[n]=null;inv.bombs--;}}
else if(shot){if((b.y==shoty||b.y==shoty+1)&&Math.abs(b.x-shotx)<2){b=bomb[n]=null;inv.bombs--;inv.score+=5;inv.shot=shot=0}}
if(b){TermlibInvaders.drawSprite(term,b.y,b.x,'V',inv.bombColor);b.y++;}}}
if(shot){if(shoty>0){if(shoty==blocky&&inv.block[shotx]){inv.block[shotx]=false;TermlibInvaders.drawSprite(term,blocky,shotx,' ',0);inv.shot=0;}
else{TermlibInvaders.drawSprite(term,shoty,shotx,'|',inv.shotColor);inv.shot++;}}
else{inv.shot=0;}}
if(moveAll){inv.invbottom=bb;}
else{inv.invleft=bl;inv.invright=br;if(dir==-1&&bl==0){inv.dir=1;}
else if(dir==1&&br==inv.maxRight){inv.dir=-1;}
inv.phase=(inv.phase==1)?2:1;}
if(isblockrow){var blockpos=inv.blockpos;for(var i=0;i<inv.blockpos.length;i++){var x=blockpos[i];if(block[x]&&term.charBuf[inv.top+blocky][inv.left+x]<=32){TermlibInvaders.drawSprite(term,blocky,x,'H',inv.blockColor);}}}},waveEnd:function(){var inv=this.env.invaders;clearTimeout(inv.timer);var drawblocks=false;var r;if(inv.phase==0){this.clear();TermlibInvaders.drawFrame.apply(this);TermlibInvaders.drawScoreBG.apply(this);TermlibInvaders.displayScore.apply(this);if(inv.width+1<this.maxCols||inv.height+1<this.maxLines){for(r=0;r<this.maxLines;r++)this.redraw(r);}
drawblocks=true;}
else{r=inv.shipY-inv.phase;TermlibInvaders.drawSprite(this,r,inv.shipX,TermlibInvaders.sprites[0],inv.shipColor);this.redraw(inv.top+r);if(inv.shipY-inv.phase==inv.blockY)drawblocks=true;}
if(inv.phase==inv.shipY){inv.timer=setTimeout(function(){TermlibInvaders.newWave.apply(inv.termref);},inv.delay);}
else{inv.phase++;r=inv.shipY-inv.phase;TermlibInvaders.drawSprite(this,r,inv.shipX,TermlibInvaders.sprites[5],inv.shipColor);this.redraw(inv.top+r);if(r==inv.blockY)drawblocks=true;if(drawblocks){var block=inv.block;var blockpos=inv.blockpos;r=inv.blockY;for(var i=0;i<inv.blockpos.length;i++){var x=blockpos[i];if(block[x]&&term.charBuf[inv.top+r][inv.left+x]<=32){TermlibInvaders.drawSprite(term,r,x,'H',inv.blockColor);}}
this.redraw(inv.top+r)}
inv.timer=setTimeout(function(){TermlibInvaders.waveEnd.apply(inv.termref);},Math.max(10,inv.delay*2-inv.phase*2));}},gameOver:function(){var inv=this.env.invaders;clearTimeout(inv.timer);if(inv.phase>=TermlibInvaders.sprites.length){TermlibInvaders.writeToCenter.apply(this,[TermlibInvaders.gameOverScreen,TermlibInvaders.gameOverScreenWidth]);this.lock=false;this.handler=TermlibInvaders.splashScreenHandler;}
else{TermlibInvaders.drawSprite(this,inv.shipY,inv.shipX,TermlibInvaders.sprites[inv.phase++],inv.shipHitColor);this.redraw(inv.top+inv.shipY);inv.timer=setTimeout(function(){TermlibInvaders.gameOver.apply(inv.termref);},inv.delay*3);}},keyHandler:function(){var inv=this.env.invaders;var key=this.inputChar;if(key==this.termKey.ESC||key==113){TermlibInvaders.exit.apply(this);}
else if(key==112||inv.paused){TermlibInvaders.pause.apply(this);}
else if(key==this.termKey.LEFT||key==104){if(inv.shipX>0)inv.smove=-1;return;}
else if(key==this.termKey.RIGHT||key==108){if(inv.shipX<inv.maxRight)inv.smove=1;return;}
else if(key==32){if(inv.shot==0){inv.shot=1;inv.shotX=inv.shipX+3;}}},pause:function(){var inv=this.env.invaders;clearTimeout(inv.timer);inv.paused=!inv.paused;var text=(inv.paused)?' *** P A U S E D *** ':'                     ';this.typeAt(Math.floor(this.maxLines/2)-2,Math.floor((this.maxCols-text.length)/2),text,inv.alertColor);if(!inv.paused)TermlibInvaders.mainLoop.apply(this);},drawSprite:function(termref,r,c,t,s){var inv=termref.env.invaders;r+=inv.top;c+=inv.left;var cb=termref.charBuf[r];var sb=termref.styleBuf[r];for(var i=0;i<t.length;i++,c++){cb[c]=t.charCodeAt(i);sb[c]=s;}
inv.changed[r]=true;},drawScoreBG:function(){var inv=this.env.invaders;var srs=this.styleBuf[inv.statusRow];var src=this.charBuf[inv.statusRow];var clr=inv.statusColor|1;for(var c=inv.left;c<inv.right;c++){srs[c]=clr;src[c]=0;}},displayScore:function(){var inv=this.env.invaders;var text='Invaders | "q","esc": quit "p": pause |  Wave: '+inv.wave+'  Score: '+inv.score;var x=inv.left+Math.floor((inv.width-text.length)/2);var b=this.charBuf[inv.statusRow];for(var i=0;i<text.length;i++)b[x+i]=text.charCodeAt(i);this.redraw(inv.statusRow);},writeToCenter:function(buffer,bufferWidth){var sx=Math.max(0,Math.floor((this.maxCols-bufferWidth)/2));var sy=Math.max(0,Math.floor((this.maxLines-buffer.length)/2));for(var i=0;i<buffer.length;i++){this.cursorSet(sy+i,sx);this.write(buffer[i]);}},buildScreen:function(){this.clear();var inv=this.env.invaders;if(inv.maxCols>0&&this.maxCols>inv.maxCols){inv.width=inv.maxCols;inv.left=Math.floor((this.maxCols-inv.maxCols)/2);inv.right=inv.left+inv.width;}
else{inv.width=inv.right=this.maxCols;inv.left=0;}
if(inv.maxRows>0&&this.maxLines>inv.maxRows){inv.height=inv.maxRows;inv.top=Math.floor((this.maxLines-inv.maxRows)/2);inv.bottom=inv.top+inv.height;}
else{inv.height=inv.bottom=this.maxLines;inv.top=0;}
inv.shipCenter=Math.floor((inv.width-3)/2);inv.statusRow=inv.bottom-1;inv.maxRight=inv.width-7;inv.shipY=inv.height-3;inv.bombMaxY=inv.height-7;inv.blockY=inv.height-5;},drawFrame:function(){var inv=this.env.invaders;if(TermlibInvaders.frameChar){var r0,r1,i;var c=TermlibInvaders.frameChar.charCodeAt(0);var cc=inv.frameColor;if(inv.height+1<this.maxLines){r0=Math.max(inv.left-1,0);r1=Math.min(inv.right+1,this.maxCols);var cb1=this.charBuf[inv.top-1];var sb1=this.styleBuf[inv.top-1];var cb2=this.charBuf[inv.bottom];var sb2=this.styleBuf[inv.bottom];for(i=r0;i<r1;i++){cb1[i]=cb2[i]=c;sb1[i]=sb2[i]=cc;}}
if(inv.width+1<this.maxCols){r0=Math.max(inv.top-1,0);r1=Math.min(inv.bottom+1,this.maxLines);var p1=inv.left-1;var p2=inv.right;for(i=r0;i<r1;i++){var b=this.charBuf[i];b[p1]=b[p2]=c;b=this.styleBuf[i];b[p1]=b[p2]=cc;}}}},exit:function(){this.clear();var inv=this.env.invaders;this.handler=inv.termHandler;if(inv.charBuf){for(var r=0;r<inv.charBuff.length;r++){var tr=this.maxLines-1;this.charBuf[tr]=inv.charBuf[r];this.styleBuf[tr]=inv.styleBuf[r];this.redraw(tr);this.maxLines--;}}
if(inv.termMaxCols>=0)this.maxCols=inv.termMaxCols;this.keyRepeatDelay1=inv.keyRepeatDelay1;this.keyRepeatDelay2=inv.keyRepeatDelay2;delete inv.termref;this.lock=false;this.charMode=inv.charMode;delete this.env.invaders;this.prompt();},getStyleColorFromHexString:function(clr){var cc=Terminal.prototype.globals.webifyColor(clr.replace(/^#/,''));if(cc){return Terminal.prototype.globals.webColors[cc]*0x10000;}
return 0;}};TermGlobals.assignStyle(32,'m','<a class="tlink" href="http://www.masswerk.at">','<\/a>');TermGlobals.assignStyle(64,'n','<a class="tlink" href="http://cb.vu/unixtoolbox.xhtml">','<\/a>');var helpPage=['%c(@beige)This is a Unix-like virtual shell command line interface.','Every command can be bookmarked with the # sign, for example: http://cb.vu/#clock -t or http://cb.vu/#matrix','Pipes are not implemented sorry, maybe one day I\'ll add emacs, but there is vi! :o)','This terminal uses the termlib library from Norbert Landsteiner <%+mhttp://www.masswerk.at%-m>,','the commands implementation and overall site are developed by Colin Barschel.','See "info" for the full credentials.','','Some commands:','   %c(@chartreuse)help%c(@darkgray)   .  .  .  .  .  .  .  %c(@beige)print this help page','   %c(@chartreuse)info%c(@darkgray)   .  .  .  .  .  .  .  %c(@beige)display code credentials','   %c(@chartreuse)exit%c(@darkgray)   .  .  .  .  .  .  .  %c(@beige)leave the terminal (same as logout or ESC key)','   %c(@chartreuse)reload%c(@darkgray) .  .  .  .  .  .  .  %c(@beige)reload the web page and the terminal','   %c(@chartreuse)redim%c(@darkgray)  .  .  .  .  .  .  .  %c(@beige)redimention the terminal size to the browser window','   %c(@chartreuse)login%c(@darkgray)  .  .  .  .  .  .  .  %c(@beige)login as a different user (passwd = username)','   %c(@chartreuse)snake%c(@darkgray)  .  .  .  .  .  .  .  %c(@beige)a variation of the classical snake game','   %c(@chartreuse)invaders%c(@darkgray)  .  .  .  .  .  .  %c(@beige)invaders! Thanks to Norbert Landsteiner','   %c(@chartreuse)clock%c(@darkgray)  .  .  .  .  .  .  .  %c(@beige)display a large ASCII clock (or stopwatch with option -t)','   %c(@chartreuse)vi <file>%c(@darkgray) .  .  .  .  .  .  %c(@beige)edit or create a file with vi','   %c(@chartreuse)echo "text" > <file>%c(@darkgray)  .  .  %c(@beige)create a file with some text','   %c(@chartreuse)cat <file>%c(@darkgray)   .  .  .  .  .  %c(@beige)display the file on the terminal','   %c(@chartreuse)more <file>%c(@darkgray)  .  .  .  .  .  %c(@beige)display the file on the terminal with pagewise output','   %c(@chartreuse)pr <file>%c(@darkgray) .  .  .  .  .  .  %c(@beige)load the file on the browser (same as cb.vu/<file>)','   %c(@chartreuse)apropos <command>%c(@darkgray)  .  .  .  %c(@beige)display a short info on the command','   %c(@chartreuse)man <command>%c(@darkgray)   .  .  .  .  %c(@beige)man pages for the command. See also ls /usr/share/man','   %c(@chartreuse)ssh [user@host]%c(@darkgray) .  .  .  .  %c(@beige)ssh to any host using self-signed mindterm','   %c(@chartreuse)whereami%c(@darkgray)  .  .  .  .  .  .  %c(@beige)display your probable country and city','   %c(@chartreuse)weather%c(@darkgray)   .  .  .  .  .  .  %c(@beige)display a weather information based on your location','   %c(@chartreuse)matrix%c(@darkgray) .  .  .  .  .  .  .  %c(@beige)show a matrix-like screensaver (CPU hungry)','   %c(@chartreuse)reboot%c(@darkgray) .  .  .  .  .  .  .  %c(@beige)reboot (root only)','   %c(@chartreuse)chat%c(@darkgray)   .  .  .  .  .  .  .  %c(@beige)chat with the terminal','   %c(@chartreuse)ls /bin%c(@darkgray)   .  .  .  .  .  .  %c(@beige)listing of all commands','   %c(@chartreuse)cal,ls,cd,pwd,fortune,uname,uptime,ping,date,history%c(@beige)...  and so on should work','','Have a lot of fun... Oh and this site is bug free (of course :o)), still tell me if you crash it.'];var infoPage=['%c(@beige)This console is implemented with the javascript terminal library termlib.js.','Termlib and the invaders game are developed by:','  (c) Norbert Landsteiner 2003-2007','  mass:werk - media environments','  <%+mhttp://www.masswerk.at%-m>','','The GeoIP location information is provided by "MaxMind"','  <http://www.maxmind.com>.','','The weather data is provided by "The Weather Channel Interactive, Inc."','  <http://www.weather.com>.','','The chatbot is adapted from the Alkali Chatbot."','  <http://www.alkalisoftware.ca>.','','The rest of this site <cb.vu> and the additional shell and command code','are developed by:','  (c) Colin Barschel 2007-2008','  c@cb.vu','  <http://cb.vu>','','Thanks for looking around and have fun. Drop me a note if you liked this site.'];var asciinumber=['    .XEEEEb          ,:LHL         :LEEEEEG       .CNEEEEE8              bMNj       NHKKEEEEEX          1LEEE1    KEEEEEEEKNMHH      8EEEEEL.        cEEEEEO    ','   MEEEUXEEE8      jNEEEEE        EEEEHMEEEEU     EEEELLEEEEc           NEEEU      7EEEEEEEEEK       :EEEEEEN,    EEEEEEEEEEEEE    OEEEGC8EEEM     1EEELOLEEE3  ','  NEE.    OEEC     EY" MEE        OC      LEEc    :"      EEE          EEGEE3      8EN              MEEM.                  :EE.   1EEj     :EEO   1EE3     DEEc ',' ,EEj      EEE         HEE                 EEE            cEE:        EEU EEJ      NEC             EEE                     EEJ    EEE       EEE   EEN       KEE ',' HEE       jEE1        NEE                 EEE            EEE        EEM  EEJ      EE             LEE   ..                EEK     DEEj     :EE7  ,EE1       jEE ',' EEH        EEZ        KEE                :EE1      .::jZEEG        EEU   EEJ     .EEEEEENC       EE77EEEEEEL            NEE       UEENj  bEE7   .EEX       :EE.','.EEZ        EEM        KEE                EEK       EEEEEEC       .EEc    EEC     :X3DGMEEEEU    3EEEED.".GEEE.         CEE.         EEEEEEE      EEEj     :EEE ',' EEZ        EEM        KEE              :EEK           "jNEEZ    :EE      EE7             MEEU   LEEb       EEE        .EE8        DEEL:.8EEEM     NEEENMEEEHEE ',' EEN       .EEG        KEE             bEEG               7EEM  jEEN738ODDEEM3b            EEE   MEE        8EE,       EEE        EEE      ,EEE     .bEEEEC XEE ',' LEE       3EE:        KEE           .EEE,                 EEE  LEEEEEEEEEEEEEE            XEE   8EE        cEE:      NEE        7EE1       jEE1           :EE: ',' .EEc      EEE         KEE          bEED                   EEE            EE1              EEE    EEX       EEE      3EE:        cEEc       7EEj          CEEG  ','  MEE7    NEE.         EEE        jEEK            C       EEE1            EEC     j      :EEE     CEEG     LEEj     .EEU          EEE:     .EEE         1EEEJ   ','   bEEEEEEEE.          EEE       NEEEEEEEEEEEE   bEEEEEEEEEE7             EEd    JEEEEEEEEEN       jEEEEEEEEE7     .EEE            KEEEEHEEEEL     8EEEEEEX     ','     DEEEL7            CGD       3GD3DOGGGGGUX    :DHEEEN8.               bUd     7GNEEEMc           7LEEEX:       1XG               JHEEEM1       COLIN"       '];var asciinumber_s=['.oPYo.  .o    .oPYo. .oPYo.    .8  oooooo .pPYo. oooooo  .PY.  .oPYo. ','8   o8   8        `8     `8   d"8  8      8         .o"  8  8  8"  `8 ','8  P 8   8       oP"   .oP"  d" 8  8pPYo. 8oPYo.   .o"  .oPYo. 8.  .8 ','8 d  8   8    .oP"      `b. Pooooo     `8 8"  `8  .o"   8"  `8 `YooP8 ','8o   8   8    8"         :8     8      .P 8.  .P .o"    8.  .P     .P ','`YooP"  o8o   8ooooo `YooP"     8  `YooP" `YooP" o"     `YooP" `YooP" '];var asciiddot=['     ','     ','     ',' @@  ',' @@  ','     ','     ','     ','     ','     ',' @@  ',' @@  ','     ','     ','     '];var asciiddot_s=['  ','x ','  ','  ','x ','  '];var asciin=asciinumber;var asciid=asciiddot;var bs=['%c(@white)A problem has been detected and windows has been shut down to prevent','damage to your computer.','','PAGE_FAULT_BECAUSE_OF_DUMB_USER','','Suggestions: Restart computer, if problems continue, install Linux.','','Technical Information:','','***STOP: 0x00000050 (0xBD32E7E4, 0x00000011, 0x8B5F87F4,0x00000012)','','Physical memory was dumped.'];var safari=false;var fortuneid=1;var shortm=['Jan','Feb','Mar','Apr','Mai','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];var path="/home/www";var files_root_n=['bin','etc','home','tmp','sbin','usr','var'];var files_root_s=['   512','   512','  1024','   512','   512','   512','   512'];var files_root_t=['Jan 23 00:13','Feb  2 14:36','Jan 23 00:13','Nov 10  2007','Nov 10  2007','Nov 10  2007','Nov 10  2007'];var files_root=[files_root_n,files_root_s,files_root_t,'%c(@lightgrey)drwxr-x---   1 root  wheel'];var files_etc_n=['passwd','group','rc.conf','master.passwd','hosts','crontab'];var files_etc_s=['   766','   266','  3061','  3960','   766','  1852'];var files_etc_t=['Jan 23 00:13','Jan 23 00:13','Feb  2 14:36','Jan 23 00:13','Nov 10  2007','Nov 26 17:28'];var files_etc=[files_etc_n,files_etc_s,files_etc_t,'%c(@lightgrey)-rw-r-----   1 root  wheel'];var files_home_n=['www'];var files_home_s=['   766'];var files_home_t=['Nov 10  2007'];var files_home=[files_home_n,files_home_s,files_home_t,'%c(@lightgrey)drwxr-xr-x   1 root  wheel'];var files_tmp_n=['test'];var files_tmp_s=['   512'];var files_tmp_t=['Jun 11  2007'];var files_tmp=[files_tmp_n,files_tmp_s,files_tmp_t,'%c(@lightgrey)drwxrwx---   1 root  wheel'];var files_var_n=['log'];var files_var_s=['   512'];var files_var_t=['Jun 11  2007'];var files_var=[files_var_n,files_var_s,files_var_t,'%c(@lightgrey)drwxrwx---   1 root  wheel'];var files_usr_n=['share'];var files_usr_s=['   512'];var files_usr_t=['Oct 21  2006'];var files_usr=[files_usr_n,files_usr_s,files_usr_t,'%c(@lightgrey)drwxrwxr-x   1 root  wheel'];var files_share_n=['man'];var files_share_s=['   512'];var files_share_t=['Oct 21  2006'];var files_share=[files_share_n,files_share_s,files_share_t,'%c(@lightgrey)drwxrwxr-x   1 root  wheel'];var files_man_n=['echo','cal','clock','ed','hostname','invaders','ls','matrix','redim','reload','reset','snake','ssh','vi','weather','whereami'];var files_man_s=['   252','   287','   352','  1173','   281','   361','   292','   291','   451','   353','   198','   358','   232','   216','   112','  3412'];var files_man_t=['Nov 21  2007','Jan 31  2008','Nov 21  2007','Nov 21  2007','Nov 21  2007','Nov 21  2007','Nov 21  2007','Jul 10  2008','Nov 21  2007','Nov 21  2007','Apr 17  2008','Feb 01  2008','Feb 11  2008','Apr 02  2008','Nov 21  2007','Mar 21  2008'];var files_man=[files_man_n,files_man_s,files_man_t,'%c(@lightgrey)-rw-rw-r--   1 root  wheel'];var files_bin_n=['apropos','browse','browser','cal','cat','chat','clear','clock','cd','date','df','echo','ed','fortune','history','hostname','help','id','info','invaders','ll','logout','ls','man','matrix','more','ping','ps','pwd','pr','reload','snake','ssh','sudo','redim','reset','top','uname','whereami','rm','time','uptime','vi','who','weather','whoami'];var files_bin_s=['  1933','  3061','  3960','   766','  1150','  2170','  1176','  1834','  1650',' 81933','   695','  1507','  1327','  1127','  1852','  1140','  1933','   256','  1678',' 32648','  5150','  1232','  5150','   593','  3595','  1698','  2668','  1668','  3855','  7159','  1353','  3695','  1435','  1135','  1156','   815','   193','  2565','  5466','  9331','  1357','   150',' 19364','  8364','   384','  1744'];var files_bin_t=['Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 22  2006','Oct 22  2006','Oct 21  2006','Oct 21  2006','Apr 11  2008','Jul 11  2008','Oct 21  2006','Oct 21  2006','Feb 10  2008','Feb 10  2008','Oct 21  2006','Jun 11  2007','Jun 11  2007','Apr 11  2008','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Jan 28  2008','Jan 25  2008','Oct 21  2006','Oct 21  2006','Jun 11  2007','Oct 21  2006','Apr  5  2008','Oct 21  2006','Jan 21  2008','Oct 21  2006','Nov 10  2007','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Apr  4 02:46','Jan 23 00:13','Jan 23 00:13','Feb 10 01:24'];var files_bin=[files_bin_n,files_bin_s,files_bin_t,'%c(@lightgrey)-rwxr-x--x   1 root  wheel'];var files_sbin_n=['sysctl','netstat','browser','passwd','ifconfig','route','mount','reboot','halt','shutdown','su'];var files_sbin_s=['  1933','  3061','  3960','   766','  1150','  1350','  1834','  1650','   933','   695','  1507'];var files_sbin_t=['Feb 10 01:23','Feb 10 01:24','Oct 21  2006','Oct 24  2006','Oct 21  2006','Oct 21  2006','Oct 21  2006','Jun 11  2007','Oct 21  2006','Oct 21  2006','Oct 21  2006'];var files_sbin=[files_sbin_n,files_sbin_s,files_sbin_t,'%c(@lightgrey)-rwxr-x---   1 root  wheel'];var files_www_n=['about.txt','bugs.txt','cb.txt','exploring.gif','favicon.ico','index.html','shell.js','sitemap.xml','termlib.js','termlib_invaders.js','termlib_parser.js','unixtoolbox.book.pdf','unixtoolbox.book2.pdf','unixtoolbox.pdf','unixtoolbox.txt','%+nunixtoolbox.xhtml%-n'];var files_www_s=['  1045','   954','  4033',' 98166','  1150','  1933',' 25695','   766',' 64720',' 21371','  6036','272458','271664','345472','124113','191701'];var files_www_t=['Feb 15 01:31','Apr 18 00:31','Feb 11 02:06','Jul 23  2004','Jan 31 15:17','Feb 10 16:53','Feb  5 00:52','Feb 10 01:13','Feb 10 01:13','Feb 10 02:14','Feb 10 02:14','Apr 09 02:14','Apr 09 02:14','Apr 09 04:20','Apr 09 01:47','Apr 09 02:50'];var files_www=[files_www_n,files_www_s,files_www_t,'%c(@lightgrey)-rw-r-----   1 colin   www'];var tree=['/','/etc','/tmp','/bin','/home','/home/www','/sbin','/usr','/usr/share','/usr/share/man','/var'];var tree_files=[files_root,files_etc,files_tmp,files_bin,files_home,files_www,files_sbin,files_usr,files_share,files_man,files_var];var remote_files=['unixtoolbox.xhtml','unixtoolbox.txt','index.html','shell.js','termlib.js','termlib_parser.js','termlib_invaders.js'];var binary_files=['unixtoolbox.pdf','unixtoolbox.book.pdf','unixtoolbox.book2.pdf','exploring.gif','favicon.ico'];var filesContent=[];filesContent["/boot/shutdown"]=['%c(@lightgrey)Waiting (max 60 seconds) for system process \'crypto\' to stop...done','%c(@lightgrey)Waiting (max 60 seconds) for system process \'vnlru\' to stop...done','%c(@lightgrey)Waiting (max 60 seconds) for system process \'bufdaemon\' to stop...done','%c(@lightgrey)Waiting (max 60 seconds) for system process \'syncer\' to stop...','%c(@lightgrey)Syncing disks, vnodes remaining...5 6 7 3 2 1 1 1 0 0 0 done','','','','%c(@lightgrey)All buffers synced.','%c(@lightgrey)Uptime: 14d13h29m45s','','%c(@lightgrey)Rebooting...%n','','',''];filesContent["/boot/kernel"]=['%c(@lightgrey)CB.VU ROM BIOS Version 1.34 A12','%c(@lightgrey)Copyright 2007-2008 Colin Barschel All Rights Reserved','','%c(@lightgrey)FreeBSD 7.1-STABLE #3: Sat Feb 16 16:14:11 CET 2009','%c(@lightgrey)  sysad@cb.vu:/usr/obj/usr/src/sys/CB','','%c(@lightgrey)Timecounter "i8254" frequency 1193182 Hz quality 0','%c(@lightgrey)CPU: Dual Core AMD Opteron(tm) Processor 270    (2010.31-MHz K8-class CPU)','%c(@lightgrey)  Origin = "AuthenticAMD"  Id = 0x20f12  Stepping = 2','%c(@lightgrey)  Features=0x178bfbff<FPU,VME,DE,PSE,TSC,MSR,PAE,MCE,CX8,APIC,SEP,MTRR,PGE,MCA,CMOV,PAT,PSE36,CLFLUSH,MMX,FXSR,SSE,SSE2,HTT>','%c(@lightgrey)  Features2=0x1<SSE3>','%c(@lightgrey)  AMD Features=0xe2500800<SYSCALL,NX,MMX+,FFXSR,LM,3DNow!+,3DNow!>','%c(@lightgrey)  AMD Features2=0x3<LAHF,CMP>','%c(@lightgrey)  Cores per package: 2','%c(@lightgrey)usable memory = 2139738112 (2040 MB)','%c(@lightgrey)avail memory  = 2065133568 (1969 MB)','','%c(@lightgrey)Detecting IDE drives ... IDE Flash Disk','','%c(@lightgrey)acpi0: <Nvidia AWRDACPI> on motherboard','%c(@lightgrey)acpi_timer0: <24-bit timer at 3.579545MHz> port 0x808-0x80b on acpi0','%c(@lightgrey)ata0: <ATA channel 0> on atapci0','%c(@lightgrey)ata1: <ATA channel 1> on atapci0','%c(@lightgrey)usb0: <SiS 5571 USB controller> on ohci0','%c(@lightgrey)usb0: USB revision 2.0','%c(@lightgrey)uhub0: SiS OHCI root hub, class 9/0, rev 1.00/1.00, addr 1','%c(@lightgrey)cpu0: <ACPI CPU> on acpi0','%c(@lightgrey)cpu1: <ACPI CPU> on acpi0','%c(@lightgrey)bge0: <Broadcom NetXtreme Gigabit Ethernet Controller, ASIC rev. 0x3003> mem 0xfe9e0000-0xfe9effff irq 18 at device 6.0 on pci1','%c(@lightgrey)Looks convincing, doesn\'t it?','%c(@lightgrey)atapci0: <SiS 962/963 UDMA133 controller> port 0x1f0-0x1f7,0x3f6,0x170-0x177at device 2.5 on pci0%n','%c(@lightgrey)Timecounters tick every 1.000 msec','','%c(@lightgrey)ipfw2 (+ipv6) initialized, divert enabled, rule-based forwarding disabled, default to deny, logging enabled','%c(@lightgrey)Trying to mount root from ufs:/dev/ad0a','','%c(@lightgrey)/bin/sh: accessing tty1','%c(@lightgrey)Starting external programs: ssh apache2 mxvpn sendmail','','ready',' '];filesContent["/etc/passwd"]=['%c(@lightgrey)# $FreeBSD: src/etc/master.passwd,v 1.40 2005/06/06 20:19:56 brooks Exp $','#','root:*:0:0:Charlie &:/root:/bin/csh','toor:*:0:0:Bourne-again Superuser:/root:','mailnull:*:26:26:Sendmail Default User:/var/spool/mqueue:/usr/sbin/nologin','www:*:80:80:World Wide Web Owner:/nonexistent:/usr/sbin/nologin','sysa:*:1001:0:System Administrator:/home/sysadmin:/bin/tcsh'];filesContent["/etc/group"]=['%c(@lightgrey)# $FreeBSD: src/etc/group,v 1.32.2.1 2006/03/06 22:23:10 rwatson Exp $','#','wheel:*:0:root,sysa','mailnull:*:26:milter','www:*:80:','sysa:*:1001:'];filesContent["/etc/rc.conf"]=['%c(@lightgrey)hostname="cb.vu"','firewall_enable="YES"              # Set to YES to enable firewall functionality','firewall_type="web"                # Firewall type (see /etc/rc.firewall)','ifconfig_rl0="inet 78.31.70.238  netmask 255.255.255.0"','defaultrouter="78.31.70.1"','sshd_enable="YES"                  # Enable sshd','sendmail_enable="YES"              # Run the sendmail inbound daemon (YES/NO).','sendmail_flags="-L sm-mta -bd -q30m"','apache22_enable="YES"              # start Apache httpd','apache22ssl_enable="YES"','apache22_http_accept_enable="YES"  # Use kernel accf_data and accf_http modules'];filesContent["/etc/hosts"]=['%c(@lightgrey)# In the presence of the domain name service or NIS, this file may','# not be consulted at all; see /etc/nsswitch.conf for the resolution order.','#','::1                     localhost localhost.cb.vu','127.0.0.1               localhost localhost.cb.vu'];filesContent["/etc/crontab"]=['%c(@lightgrey)# $FreeBSD: src/etc/crontab,v 1.32 2002/11/22 16:13:39 tom Exp $','#minute hour    mday    month   wday    who     command','# Save some entropy so that /dev/random can re-seed on boot.','*/11    *       *       *       *       operator /usr/libexec/save-entropy','# Rotate log files every hour, if necessary.','0       *       *       *       *       root    newsyslog','# Perform daily/weekly/monthly maintenance.','1       3       */2     *       *       root    periodic daily','15      4       */2     *       6       root    periodic weekly','30      5       1       */2     *       root    periodic monthly'];filesContent["/usr/share/man/weather"]=['%c(@lightcyan)WEATHER                  CB.VU General Commands Manual                WEATHER','','NAME','     weather -- display weather information or forecast based on your location','','SYNOPSIS','     weather [-i -f] [city,country]','','DESCRIPTION','     The weather command displays the weather information based either on the','     city/country given as argument, or based on the IP address location. The','     IP location is retrieved with the whereami command. The units are metric','     per default and can be changed to imperial with -i.','','     The options are as follows:','','     -i      Display imperial units or U.S. system or something like that.','','     -f      Weather forecast for the night and next day.','','     [city,country] The whole city and country has to be quoted if any of them','             has a space in its name. See the examples','','EXAMPLES','%c(@chartreuse)weather                              %c(@lightcyan)# weather based on the IP location','%c(@chartreuse)weather -i "London,United Kingdom"   %c(@lightcyan)# use imperial units','%c(@chartreuse)weather -f Geneva,Switzerland        %c(@lightcyan)# Night and next day forecast','%c(@chartreuse)weather -i -f "San Francisco,United States"%c(@lightcyan)','','SEE ALSO','     whereami'];filesContent["/usr/share/man/whereami"]=['%c(@lightcyan)WHEREAMI                 CB.VU General Commands Manual               WHEREAMI','','NAME','     whereami -- display your probable position based on you public IP','','DESCRIPTION','     The geographic position of your IP is provided by "MaxMind" using the','     web service. See http://www.maxmind.com. The accuracy is limited by the','     provider information and extension of the IP range. Also sometimes it','     simply does not work...','','SEE ALSO','     weather'];filesContent["/usr/share/man/vi"]=['%c(@lightcyan)VI                       CB.VU General Commands Manual                     VI','','NAME','     Vi -- a screen oriented text editor.','','DESCRIPTION','     Vi is a modal editor and is either in insert mode or err... Who doesn\'t ','     know vi? This implementation is very thin and does not support paging. That','     is only the visible page can be edited. The following commands should work:','','     <ESC>        to enter command mode','     :q<Enter>    to exit','     :w<Enter>    to save','     :w filename  to save to "filename"','     :e filename  to open "filename"','     :q!<Enter>   to exit without saving','     D            to delete rest of line','     dd           to delete current line','     x            to delete current char','     i            to enter edit mode    ','     UP RIGHT DOWN LEFT to move the cursor','     or h left  j down  k up  l right','','     %c(@chartreuse)On Safari browsers use <TAB> instead of <ESC>!!%c(@lightcyan)'];filesContent["/usr/share/man/ssh"]=['%c(@lightcyan)SSH                      CB.VU General Commands Manual                    SSH','','NAME','     ssh -- Mindterm SSH client (remote login program)','','SYNOPSIS','     ssh [-L port:host:hostport] [-p port] [user@]hostname','','DESCRIPTION','     The ssh command will start the Appgate java applet "mindterm".','     The applet is self-signed and can thus be used to connect to any server','     (as you don\'t have an account on cb.vu...)','     and also allows to build tunnels. This is the compiled version from','     www.appgate.com with the logo removed.','     %c(@chartreuse)There is no connection between this client and the cb.vu server.','','     Use the top right "X" to close the ssh client%c(@lightcyan)','','EXAMPLES','     ssh hostname','     ssh -p 123 user@hostname','     ssh -L 3128:127.0.0.1:80 -p 1234 user@hostname'];filesContent["/usr/share/man/echo"]=['%c(@lightcyan)ECHO                     CB.VU General Commands Manual                   ECHO','','NAME','     echo -- write arguments to the standard output','','DESCRIPTION','     The echo utility writes any specified operands, separated by single blank','     (\' \') characters and followed by a newline (\\n) character, to the stan-','     dard output.','     the > redirect can be used to create a file. For example the command','     %c(@chartreuse)echo Hello world! > hello.txt%c(@lightcyan)','     will create the file hello.txt'];filesContent["/usr/share/man/hostname"]=['%c(@lightcyan)HOSTNAME               CB.VU General Commands Manual                 HOSTNAME','','NAME','     hostname -- print name of current host system','','SYNOPSIS','     hostname [-fsi]','','DESCRIPTION','     The hostname utility prints the name of the current host.','     This script uses the hostname variable in /etc/rc.conf.','','     Options:','','     -f    Include domain information in the printed name.  This is the','           default behavior.','','     -s    Trim off any domain information from the printed name.','','     -i    Show the corresponding host IP address.'];filesContent["/usr/share/man/reload"]=['%c(@lightcyan)RELOAD                 CB.VU General Commands Manual                   RELOAD','','NAME','     reload -- reload the terminal as a new http request','','DESCRIPTION','     This command will reload the terminal with a new http GET request from the','     browser. This will reinitialize the shell and all variables and has the same ','     effect as the browser reload button. A reload will also recalculate the shell','     size.','','SEE ALSO','     reset, redim'];filesContent["/usr/share/man/reset"]=['%c(@lightcyan)RESET                  CB.VU General Commands Manual                    RESET','','NAME','     reset -- reset the terminal to the initial state','','DESCRIPTION','     This command will reset the terminal to its initial state but will not reload','     the variables. The created file are not deleted either. Delete them all with','     rm * in the home directory.','','SEE ALSO','     reload'];filesContent["/usr/share/man/redim"]=['%c(@lightcyan)REDIM                  CB.VU General Commands Manual                    REDIM','','NAME','     redim -- calculates and resizes the shell to it\'s maximal size.','','DESCRIPTION','     This command resizes the shell to fit the visible browser area, it can be used','     when the browser size has changed. The argument <-s> will only display the sizes','     but will not change anything.','','     The following options are available:','','     -s only display the window and shell sizes without changing anything','','SEE ALSO','     reload'];filesContent["/usr/share/man/snake"]=['%c(@lightcyan)SNAKE                  CB.VU General Commands Manual                    SNAKE','','NAME','     snake -- a variation of the classical snake game.','','DESCRIPTION','     The snake must be steered to get food (the numbers randomly displayed)','     and avoid crashing on rocks or wall or itself. There is also an autopilot,','     but it is not to be trusted.','','     The following options are available:','','     -s1 for speed: -s1 = slow; -s3 = fast','     -f1 for food: -f1 = less; -f3 = more','     -o1 for obstacles: -o1 = less; -o3 = more rocks','     -a toggle the autopilot on or off. Status is displayed on the status line','     -r toggle auto-restart on or off. Status is displayed on the status line','','EXAMPLES','','     snake -f3 -o3 -a -r     max food and rocks with autopilot and auto-restart','','SEE ALSO','     invaders'];filesContent["/usr/share/man/invaders"]=['%c(@lightcyan)INVADERS               CB.VU General Commands Manual                  INVADERS','','NAME','     invaders -- the classical invaders game, courtesy of Norbert Landsteiner.','','DESCRIPTION','     The invaders must be shot down before they reach earth. The ship must also','     avoid the enemy fire.','','     On a large screen the game might be too stretched and thus too easy to','     win... Use option -s to reduce the available game area to the classical','     80x25 characters.','','     -s start the game with a smaller area of 80x25 characters','','SEE ALSO','     snake'];filesContent["/usr/share/man/ls"]=['%c(@lightcyan)LS                     CB.VU General Commands Manual                       LS','','NAME','     ls -- list directory contents','','SYNOPSIS','     ls [-la] [directory]','','DESCRIPTION','     For each operand that names a directory, ls displays the names of files','     contained within that directory, as well as any requested, associated','     information.','     If no operands are given, the contents of the current directory are dis-','     played.','','     The following options are available:','','     -l List files in the long format with date and permission information','','     -a Display also hidden files and folders'];filesContent["/usr/share/man/matrix"]=['%c(@lightcyan)MATRIX                 CB.VU General Commands Manual                   MATRIX','','NAME','     matrix -- a matrix like screen saver animation','','SYNOPSIS','     matrix [-s]','','DESCRIPTION','     This animation displays falling random letters in a gree gradient. This is','     quite heavy on the browser rendering engine and thus uses a lot of CPU.','','     The following options are available:','','     -s Start with an empty page and fill it with time. Default starts with a newly','        generated screen.','','     key <q> or <ESC> to quit the animation','','     key <space> to pause or play the animation','','     any other key will add an iteration'];filesContent["/usr/share/man/cal"]=['%c(@lightcyan)CAL                    CB.VU General Commands Manual                      CAL','','NAME','     cal -- a simple month calender','','SYNOPSIS','     cal [n] (n = 1-12)','','DESCRIPTION','     Display a calender of the current month or an other month given as option.','','     The following options are available:','','     n  Selects an other month of the year. For example:','        Jan = 1, Jan next year = 13, Dec last year = 0'];filesContent["/usr/share/man/clock"]=['%c(@lightcyan)CLOCK                  CB.VU General Commands Manual                    CLOCK','','NAME','     clock -- display a large clock or stopwatch','','SYNOPSIS','     clock [-t -s]','','DESCRIPTION','     With no option the command clock displays a large clock in full screen','     mode and international format, like 21:45:04. It is also possible to display','     a stopwatch. Use any key besides <space> and <r> to quit','','     The following options are available:','','     -t start in stopwatch mode','','     -s use smaller numbers. This is automatic if the terminal is too small','','     <space key> pause the display, the time is still ticking...','','     <r key>     reset the stopwatch and start again.'];filesContent["/usr/share/man/ed"]=['%c(@lightgrey)This text is straight from http://www.gnu.org/fun/jokes/ed.msg.html','%c(@lightcyan)When I log into my Xenix system with my 110 baud teletype, both vi','*and* Emacs are just too damn slow.  They print useless messages like,','\'C-h for help\' and \'"foo" File is read only\'.  So I use the editor','that doesn\'t waste my VALUABLE time.','','Ed, man!  !man ed','','ED(1)               Unix Programmer\'s Manual                ED(1)','','NAME','     ed - text editor','','SYNOPSIS','     ed [ - ] [ -x ] [ name ]','DESCRIPTION','     Ed is the standard text editor.','---','','Computer Scientists love ed, not just because it comes first','alphabetically, but because it\'s the standard.  Everyone else loves ed','because it\'s ED!','','"Ed is the standard text editor."','','And ed doesn\'t waste space on my Timex Sinclair.  Just look:','','-rwxr-xr-x  1 root          24 Oct 29  1929 /bin/ed','-rwxr-xr-t  4 root     1310720 Jan  1  1970 /usr/ucb/vi','-rwxr-xr-x  1 root  5.89824e37 Oct 22  1990 /usr/bin/emacs','','Of course, on the system *I* administrate, vi is symlinked to ed.','Emacs has been replaced by a shell script which 1) Generates a syslog','message at level LOG_EMERG; 2) reduces the user\'s disk quota by 100K;','and 3) RUNS ED!!!!!!','','"Ed is the standard text editor."','','Let\'s look at a typical novice\'s session with the mighty ed:','','golem$ ed','','?','help','?','?','?','quit','?','exit','?','bye','?','hello?','?','eat flaming death','?','^C','?','^C','?','^D','?','','---','Note the consistent user interface and error reportage.  Ed is','generous enough to flag errors, yet prudent enough not to overwhelm','the novice with verbosity.','','"Ed is the standard text editor."','','Ed, the greatest WYGIWYG editor of all.','','ED IS THE TRUE PATH TO NIRVANA!  ED HAS BEEN THE CHOICE OF EDUCATED','AND IGNORANT ALIKE FOR CENTURIES!  ED WILL NOT CORRUPT YOUR PRECIOUS','BODILY FLUIDS!!  ED IS THE STANDARD TEXT EDITOR!  ED MAKES THE SUN','SHINE AND THE BIRDS SING AND THE GRASS GREEN!!','','When I use an editor, I don\'t want eight extra KILOBYTES of worthless','help screens and cursor positioning code!  I just want an EDitor!!','Not a "viitor".  Not a "emacsitor".  Those aren\'t even WORDS!!!! ED!','ED! ED IS THE STANDARD!!!','','TEXT EDITOR.','','When IBM, in its ever-present omnipotence, needed to base their','"edlin" on a Unix standard, did they mimic vi?  No.  Emacs?  Surely','you jest.  They chose the most karmic editor of all.  The standard.','','Ed is for those who can *remember* what they are working on.  If you','are an idiot, you should use Emacs.  If you are an Emacs, you should','not be vi.  If you use ED, you are on THE PATH TO REDEMPTION.  THE','SO-CALLED "VISUAL" EDITORS HAVE BEEN PLACED HERE BY ED TO TEMPT THE','FAITHLESS.  DO NOT GIVE IN!!!  THE MIGHTY ED HAS SPOKEN!!!','','?'];var pslong=['%c(@lightgrey)USER   PID %CPU %MEM   VSZ   RSS  TT  STAT STARTED      TIME COMMAND'];var globalterm;var fetcherror="";var vgeoip_country_code;var vgeoip_country_name;var vgeoip_city;var vgeoip_region;var vgeoip_latitude;var vgeoip_longitude;function incrementLoaded(t){var loaded=readCookie("loaded");loaded++;if(loaded>4){t.newLine();t.write('%c(@lightgrey)You can use "%c(@chartreuse)fortune%c(@lightgrey)" you know...%n');carriageReturn();loaded=2;}
createCookie("loaded",loaded,0,5);}
function carriageReturn(){Terminal.prototype.globals.keyHandler({which:globalterm.termKey.CR,_remapped:true});}
function pressKey(ch){Terminal.prototype.globals.keyHandler({which:ch,_remapped:true});}
function createCookie(name,value,days,min){var expires;var date=new Date();if(days){date.setTime(date.getTime()+(days*24*60*60*1000));expires="; expires="+date.toGMTString();}else{expires="";}
if(min){date.setTime(date.getTime()+(min*60*1000));expires="; expires="+date.toGMTString();}
document.cookie=name+"="+value+expires+"; path=/";}
function readCookie(name){var nameEQ=name+"=";var ca=document.cookie.split(';');for(var i=0;i<ca.length;i++){var c=ca[i];while(c.charAt(0)==' '){c=c.substring(1,c.length);}
if(c.indexOf(nameEQ)===0){return c.substring(nameEQ.length,c.length);}}
return"";}
function readAllCookies(){var nameEQ="=";var all=[];var ca=document.cookie.split(';');for(var i=0;i<ca.length;i++){var c=ca[i];while(c.charAt(0)==' '){c=c.substring(1,c.length);}
all.push(c.substring(c[0],c.indexOf(nameEQ)));}
return all;}
if(!Array.indexOf){Array.prototype.indexOf=function(obj){for(var i=0;i<this.length;i++){if(this[i]==obj){return i;}}
return-1;};}
function randomRange(min,max){if(min>max){return-1;}
if(min==max){return min;}
var r=parseInt(Math.random()*(max+1),10);return(r+min<=max?r+min:r);}
function browserWidth(){if(window.innerWidth){return window.innerWidth;}else if(document.documentElement&&document.documentElement.clientWidth){return document.documentElement.clientWidth;}else if(document.body&&document.body.offsetWidth){return document.body.offsetWidth;}else{return 0;}}
function browserHeight(){if(window.innerHeight){return window.innerHeight;}else if(document.documentElement&&document.documentElement.clientHeight){return document.documentElement.clientHeight;}else if(document.body&&document.body.offsetHeight){return document.body.offsetHeight;}else{return 0;}}
Date.prototype.getMonthName=function(){return['January','February','March','April','May','June','July','August','September','October','November','December'][this.getMonth()];};Date.prototype.milTime=function(){var t=this.getHours()+':'+this.getMinutes()+':'+this.getSeconds();return t;};Date.prototype.daysInMonth=function(){return new Date(this.getFullYear(),this.getMonth()+1,0).getDate();};Date.prototype.calendar=function(){var calArray=[];var buildStr='';var numDays=this.daysInMonth();var startDay=new Date(this.getFullYear(),this.getMonth(),1).getDay();calArray.push('%c(@lightgrey)       '+this.getMonthName()+' '+this.getFullYear());calArray.push('Sun Mon Tue Wed Thu Fri Sat');for(var i=0;i<startDay;i++){buildStr+='    ';}
var blankdays=startDay;var filler='';var j=1;for(i=1;i<=numDays;i++){if(this.getDate()==i){j='%+r'+i+'%-r';}else{j=i;}
if(i<10){buildStr+=' '+j+'  ';}else{buildStr+=j+'  ';}
blankdays++;if(((blankdays%7)===0)&&(i<numDays)){calArray.push(buildStr);buildStr='';}}
blankdays++;while((blankdays%7)!==0){buildStr+='    ';blankdays++;}
calArray.push(buildStr);return calArray;};Array.prototype.shuffle=function(){var tindex,rindex;for(var i=0;i<this.length;i++){rindex=Math.floor(Math.random()*this.length);tindex=this[i];this[i]=this[rindex];this[rindex]=tindex;}};var xmlHttp=null;if(typeof XMLHttpRequest!='undefined'){xmlHttp=new XMLHttpRequest();}
if(!xmlHttp){var xhttperr;try{xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");}catch(xhttperr){try{xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}catch(xhttperr){xmlHttp=null;}}}
function fetchHttp(url,fkt){var xhttperr;fetcherror="";if(xmlHttp){try{xmlHttp.open('GET',url,true);xmlHttp.onreadystatechange=function(){if(xmlHttp.readyState==4){fkt(xmlHttp.responseText);}};xmlHttp.send(null);}catch(xhttperr){fetcherror="Error: no network";if(fkt!=eval){globalterm.write(fetcherror);globalterm.prompt();}}}else{fkt("Error: no XMLHttpRequest. Your browser is broken!");}}
function evaljs(content){var JSONCode=document.createElement("script");JSONCode.setAttribute('type','text/javascript');JSONCode.text=content;document.body.appendChild(JSONCode);}
function displayraw(content){globalterm.write(content);globalterm.rawMode=false;globalterm.prompt();}
function displaymore(content){globalterm.clear();globalterm.write('%c(@lightgrey)'+content,true);globalterm.rawMode=false;}
function initGeoIP(){if(!window.geoip_country_code){vgeoip_country_code="N/A";vgeoip_country_name="N/A";vgeoip_city="N/A";vgeoip_region="N/A";vgeoip_latitude="N/A";vgeoip_longitude="N/A";}else{vgeoip_country_code=geoip_country_code();vgeoip_country_name=geoip_country_name();vgeoip_city=geoip_city();vgeoip_region=geoip_region();vgeoip_latitude=geoip_latitude();vgeoip_longitude=geoip_longitude();}}
function delAFile(fname){var fpath=getPath(fname);if(fpath[0]!='/home/www'){globalterm.write('%c(@lightgrey) rm '+fname+': Permission denied.');return;}
var findex=files_www_n.indexOf(fpath[1]);if(findex!=-1){if(!readCookie(fpath[1])){globalterm.write('%c(@lightgrey) rm '+fname+': Permission denied.');return;}
files_www_n.splice(findex,1);files_www_s.splice(findex,1);files_www_t.splice(findex,1);createCookie(fpath[1],'',0);}else{globalterm.write('%c(@lightgrey)'+fname+': No such file or directory.');}}
function delAllFiles(){var allcookies=readAllCookies();var deleted=0;for(var i=0;i<allcookies.length;i++){if(allcookies[i]=='clilastlog'||allcookies[i]=='style'||allcookies[i]=='broken'){continue;}
var fcontent=readCookie(allcookies[i]);var date=fcontent.slice(fcontent.length-12);if(date.length!=12){continue;}
delAFile(allcookies[i]);deleted++;}
if(deleted>0){globalterm.write('%c(@lightgrey) deleted '+deleted+' files%n');}}
function addFile(fname,fcontent,iseditor,fdate){if(typeof iseditor=='undefined'){iseditor=false;}
var error="";var fpath=getPath(fname);fname=fpath[1];if(fname=='unixtoolbox.xhtml'){error=fname+': Permission denied';if(iseditor){return'Save '+error;}else{globalterm.write('%c(@lightgrey)'+error);return error;}}
var size=fcontent.length+1;var sizestr;var datestr;if(size<10){sizestr='     '+size;}else if(size<100){sizestr='    '+size;}else if(size<1000){sizestr='   '+size;}else if(size<10000){sizestr='  '+size;}
if(typeof fdate=='undefined'){var d=new Date();var h=d.getHours();if(h<10){h='0'+h;}
var m=d.getMinutes();if(m<10){m='0'+m;}
var day=d.getDate();if(day<10){day=' '+day;}
var mo=shortm[d.getMonth()];datestr=mo+' '+day+' '+h+':'+m;}else{datestr=fdate;}
var findex=files_www_n.indexOf(fname);if(fpath[0]!='/home/www'){error=fname+': Permission denied';if(iseditor){return'Save '+error;}else{globalterm.write('%c(@lightgrey)'+error);return error;}}else if(findex!=-1){if(!readCookie(fname)){error=fname+': system file permission denied';if(iseditor){return'Save '+error;}else{globalterm.write('%c(@lightgrey)'+error);return error;}}
files_www_n[findex]=fname;files_www_s[findex]=sizestr;files_www_t[findex]=datestr;}else{files_www_n.push(fname);files_www_s.push(sizestr);files_www_t.push(datestr);}
fcontent=fcontent.replace(/;/g,'~~');fcontent=fcontent+datestr;createCookie(fname,fcontent,365);return error;}
function addAFile(fname){var fcontent=readCookie(fname);if(fcontent!==""){var cnt=fcontent.slice(0,fcontent.length-12);cnt=cnt.replace(/~~/g,";");var date=fcontent.slice(fcontent.length-12);if(date.length==12){addFile(fname,cnt,false,date);}}}
function getPath(fname){var fullpath='';var filename='';var fullname='';var rpath=path;while(fname.charAt(fname.length-1)=='/'&&fname.length>1){fname=fname.slice(0,fname.length-1);}
var slashindex=fname.lastIndexOf('/');if(slashindex==-1&&fname.charAt(0)!='.'){filename=fname;fullpath=rpath;}else{filename=fname.slice(slashindex+1);if(fname.charAt(0)=='/'){fullpath=fname.slice(0,slashindex);if(fullpath===''){fullpath='/';}}else if(fname.indexOf('..')===0){var relarray=fname.split('..');var relpath=rpath.split('/');if(relpath.length>=relarray.length){for(var i=0;i<relarray.length-1;i++){rpath=rpath.slice(0,rpath.lastIndexOf('/'));}
var lastrel=fname.lastIndexOf('../');if(lastrel!=-1){var pathtoadd=fname.slice(lastrel+3,slashindex);if(pathtoadd.length>0){fullpath=rpath+'/'+pathtoadd;}else{fullpath=rpath;}}else{fullpath=rpath;}
if(filename=='..'){filename='';fullname=fullpath;}else{fullname=fullpath+'/'+filename;}}}else{if(rpath=='/'){fullpath=rpath+fname.slice(0,slashindex);}
else{fullpath=rpath+'/'+fname.slice(0,slashindex);}}}
if(fullpath=='/'){fullname=fullpath+filename;}
else{if(fullname.length===0){fullname=fullpath+'/'+filename;}}
return[fullpath,filename,fullname];}
function longlisting(t,files,opt){if(typeof files=='undefined'){t.write('%c(@lightgrey)Error path does not exist%n');return;}
var showmore=false;var lines=[];if(typeof opt!='undefined'&&opt.indexOf('a')!=-1){t.write(['%c(@lightgrey)drwxrwxr-x   6 sysa  wheel   1024 Feb 12 03:03 ./','drwxr-xr-x  21 root  wheel    512 Jan 25 00:26 ../%n']);}
for(var i=0;i<files[0].length;i++){lines[i]=files[3]+' '+files[1][i]+' '+files[2][i]+' '+files[0][i];}
if(files[0].length>t.conf.rows-2){showmore=true;}
t.write(lines,showmore);}
function listing(t,f){if(typeof f=='undefined'){t.write('%c(@lightgrey)Error path does not exist%n');return;}
var files=f;var name_length=0;var space_divider=5;var fileslist=[];for(var i=0;i<files.length;i++){if(files[i].length>name_length){name_length=files[i].length;}}
name_length=name_length+space_divider;var dividers=Math.round((t.conf.cols-2)/name_length);var j=1;var thisline='%c(@lightgrey)';for(var k=0;k<files.length;k++){thisline+=files[k];var this_name_lenth=files[k].length;if(files[k]=='%+nunixtoolbox.xhtml%-n'){this_name_lenth=this_name_lenth-6;}
var space_missing=name_length-this_name_lenth;var space='';while(space_missing>0){space=space+' ';space_missing--;}
thisline+=space;j++;if(j>=dividers){fileslist.push(thisline);t.write(thisline+'%n');thisline='%c(@lightgrey)';j=1;}}
if(j!==0){t.write(thisline+'%n');}}
var uptimed=randomRange(10,380);var uptime=' up '+uptimed+' days, 04:32, '+
randomRange(0,10)+' users, load averages: 0.'+
randomRange(10,99)+', 0.'+randomRange(10,89)+', 0.'+randomRange(10,69);var clockvisible=false;stopwatch=false;var numh=asciin.length;var numw=asciin[0].length/10;var asciistr=[];var r;var c;var started;var now;var firstline='';var sp='ABCDEFGHIJKLMNOPQRSTUVWXYZabscdefghijklmnopqrstuvwxyz23456789#$£@';var s=' ';var dim=null;var allRows=[];var interval=0;var xperIter=0;var mcolors=['030','033','063','093','393','0c3','3c0','6c3','0f0','6f3','3f0','9f3','ff3','fff'];var regex=[];function connectionLost(){globalterm.charMode=true;globalterm.lock=true;globalterm.cursorOff();globalterm.newLine();globalterm.write("%n%c(@orange)Error: connection reset by peer%n");createCookie("broken","true",0,1);}
function cmdLogin(t){if((t.argc==t.argv.length)||(t.argv[t.argc]==='')){t.write('%c(@lightgrey)usage: login <username>');}else{t.env.getPassword=true;t.env.username=t.argv[t.argc];t.write('%c(@lightgrey)Password: ');t.rawMode=true;t.lock=false;return;}}
function cmdSu(t){t.env.getPassword=true;t.env.username='root';t.write('%c(@lightgrey)Password: ');t.rawMode=true;t.lock=false;return;}
var uid=randomRange(500,1000);function cmdId(t){var uidnow=uid;if(t.user=='www'){uidnow=80;}
else if(t.user=='root'){uidnow=0;}
t.write('%c(@lightgrey)uid='+uidnow+'('+t.user+') gid='+uidnow+'('+t.user+') groups='+uidnow+'('+t.user+')');}
function cmdUptime(t){d=new Date();t.write('%c(@lightgrey)'+d.milTime()+uptime);}
function isnumeric(str){for(var i=0;i<str.length;i++)
{var c=str.charAt(i);var a=c.charCodeAt(0);if(!(a>47&&a<58)&&!(a==45)){return false;}
if(a==45&&i!==0){return false;}}
return true;}
function cmdCal(t){if(t.argv.length==1){d=new Date();}else{if(t.argv[1]=='-h'||t.argv[1]=='--help'){t.write('%c(@lightgrey)display this months calender.%n');t.write('%c(@lightgrey)usage: cal [month]%n');return;}else if(!isnumeric(t.argv[1])){t.write('%c(@lightgrey)usage: cal [month] where [month] is numeric%n');return;}else{now=new Date();var year=now.getFullYear();var day=now.getUTCDate();var month=t.argv[1]-1;d=new Date(year,month,day);}}
t.write(d.calendar());}
function cmdLs(t){var findex=0;var fpath=null;var longlist=false;if(t.argv.length==1){listing(t,tree_files[tree.indexOf(path)][0]);return;}else if(t.argv[1]=='-l'||t.argv[1]=='-la'||t.argv[1]=='-al'){if(t.argv.length==2){longlisting(t,tree_files[tree.indexOf(path)],t.argv[1]);return;}else{longlist=true;fpath=getPath(t.argv[2]);}}else{fpath=getPath(t.argv[1]);}
findex=tree.indexOf(fpath[2]);if(findex!=-1){if(longlist){longlisting(t,tree_files[findex],t.argv[1]);}else{listing(t,tree_files[findex][0]);}}else{t.write('%c(@lightgrey)'+t.argv[1]+': No such file or directory.');}}
function cmdLl(t){if(t.argv.length==1){longlisting(t,tree_files[tree.indexOf(path)]);}else{fpath=getPath(t.argv[1]);findex=tree.indexOf(fpath[2]);if(findex!=-1){longlisting(t,tree_files[findex]);}
else{t.write('%c(@lightgrey)'+t.argv[1]+': No such file or directory.');}}}
function cmdPwd(t){t.write('%c(@lightgrey)'+path);}
function cmdCd(t){if(t.argv.length==1||t.argv[1]=='~'){path='/home/www';t.ps='['+t.user+'@cb.vu]~>';return;}else{splitpath=getPath(t.argv[1]);var findex=tree.indexOf(splitpath[2]);if(findex!=-1){path=splitpath[2];}else{t.write('%c(@lightgrey)'+splitpath[2]+': No such file or directory.');}}
if(path=='/home/www'){t.ps='['+t.user+'@cb.vu]~>';}
else{t.ps='['+t.user+'@cb.vu]'+path+'>';}}
function cmdEcho(t){if(t.argv.length!=1&&t.argv[t.argv.length-2]=='>'){var file=t.argv[t.argv.length-1];if(path!='/home/www'){t.write('%c(@lightgrey)Permission denied');return;}
var fs='';for(var i=1;i<t.argv.length-2;i++){fs+=t.argv[i];if(i+1!=t.argv.length-2){fs+=' ';}}
addFile(file,fs);}else if(t.argv.length!=1&&t.argv[1]=='$PATH'){t.write('%c(@lightgrey)/bin:/sbin:/etc');}else{var s='%c(@lightgrey)';for(var j=1;j<t.argv.length;j++){s+=t.argv[j];if(j+1!=t.argv.length){s+=' ';}}
t.write(s);}}
function isdir(dirpath){if(tree.indexOf(dirpath)!=-1){return true;}
return false;}
function isfile(filepath){var fpath=getPath(filepath);if(tree.indexOf(fpath[0])==-1){return false;}
if(tree_files[tree.indexOf(fpath[0])][0].indexOf(fpath[1])==-1){return false;}
return true;}
function rmdir(dirpath){var fpath=getPath(dirpath);for(var j=0;j<3;j++){tree_files[tree.indexOf(fpath[0])][j].splice(tree_files[tree.indexOf(fpath[0])][0].indexOf(fpath[1]),1);}
if(isdir(dirpath)){tree_files.splice(tree.indexOf(fpath[2]),1);tree.splice(tree.indexOf(fpath[2]),1);}}
function rmdirr(dirpath){if(isdir(dirpath)){var fpath=getPath(dirpath);var files=tree_files[tree.indexOf(fpath[2])][0];while(files.length>0){rmdirr(dirpath+'/'+files[files.length-1]);}}
rmdir(dirpath);}
function cmdRm(t){t.wrap=false;if(t.argv.length==1){t.write('%c(@lightgrey)usage: rm <file>');return;}
var rf=false;var rootindex=0;var dirindex=0;var fileindex=0;var filearg=t.argv[t.argv.length-1];if(t.argv.indexOf('-rf')!=-1){rf=true;}
if(t.user=='root'){if(filearg=='/'&&rf){setTimeout('connectionLost()',15000);filearg='/bin';}
var fpath=getPath(filearg);var fname=fpath[1];var lpath=fpath[0];var fullname=fpath[2];rootindex=tree.indexOf(lpath);if(rootindex!=-1){dirindex=tree.indexOf(fullname);fileindex=tree_files[rootindex][0].indexOf(fname);if(isdir(fullname)){if(rf){rmdirr(fullname);}
else{t.write('rm: cannot remove '+filearg+': Is a directory%n');}}else{if(fileindex!=-1){for(var i=0;i<3;i++){tree_files[rootindex][i].splice(fileindex,1);}}else{t.write('rm: cannot remove '+fname+': No such file or directory%n');}}}else{t.write('rm: cannot remove '+lpath+': No such file or directory%n');}}else{if(filearg=='/'){t.write('%c(@lightgrey)I\'m sorry Dave, I\'m afraid I can\'t do that.');}else if(t.argv[1]=='*'){delAllFiles();}else{delAFile(filearg);}}}
function cmdPing(t){var host;if(t.argv.length==1){host="";}
else{host=t.argv[1];}
t.rawMode=true;fetchHttp("http://cb.vu/ping.php?host="+host,displayraw);}
function cmdWhereami(t){if(typeof vgeoip_country_code=='undefined'){initGeoIP();}
t.write('%c(@lightgrey)Your IP:  '+clientip+'%n');t.write('%c(@lightgrey)Country:  %c(@chartreuse)'+vgeoip_country_name+' ('+vgeoip_country_code+')%n');t.write('%c(@lightgrey)City:     %c(@chartreuse)'+vgeoip_city+'%n');t.write('%c(@lightgrey)Position: %c(@chartreuse)'+
vgeoip_latitude+'%c(@lightgrey) (latitude) - %c(@chartreuse)'+
vgeoip_longitude+'%c(@lightgrey) (longitude)%n');}
function cmdWeather(t){t.write('%c(@lightgrey)See also %c(@chartreuse)man weather%c(@lightgrey) for more options%n');var location;var wunits='&u=m';var forecast='&f=n';var findex=-1;if(t.argv.length>=2){findex=t.argv.indexOf('-i');}
if(findex!=-1){wunits='&u=i';t.argv.splice(findex,1);findex=-1;}
if(t.argv.length>=2){findex=t.argv.indexOf('-f');}
if(findex!=-1){forecast='&f=y';t.argv.splice(findex,1);}
if(t.argv.length==1){if(typeof vgeoip_country_code=='undefined'){initGeoIP();}
if(vgeoip_city=="N/A"){t.write("%c(@orangered)Unknown city, please provide a city name as argument");return;}
location=vgeoip_city+','+vgeoip_country_name;}else{location=t.argv[1];}
t.rawMode=true;t.write('%c(@lightgrey)Trying "'+location+'"%n');fetchHttp("http://cb.vu/w.php?city="+location+wunits+forecast,displayraw);}
function cmdBrowser(t){t.write(['%c(@lightgrey)Some browser information:','IP address:          '+clientip,'Navigator:           %c(@chartreuse)'+navigator.appCodeName+' '+
navigator.appName+' '+navigator.appVersion,'%c(@lightgrey)User agent:          %c(@chartreuse)'+navigator.userAgent,'%c(@lightgrey)Operating system:    %c(@chartreuse)'+navigator.platform,'%c(@lightgrey)Page hostname:       %c(@chartreuse)'+location.hostname,'%c(@lightgrey)Screen/browser size: %c(@chartreuse)'+screen.width+'x'+screen.height+'/'+
browserWidth()+'x'+browserHeight()+' %c(@lightgrey)pixels']);}
function cmdUname(t){if(t.argv.length==1||t.argv[1]=='-s'){t.write('%c(@lightgrey)FreeBSD');}
else if(t.argv[1]=='-i'){t.write('%c(@lightgrey)CB');}
else if(t.argv[1]=='-m'||t.argv[1]=='-p'){t.write('%c(@lightgrey)i386');}
else if(t.argv[1]=='-n'){t.write('%c(@lightgrey)cb.vu');}
else if(t.argv[1]=='-a'){t.write('%c(@lightgrey)FreeBSD cb.vu 7.1-STABLE FreeBSD 7.1-STABLE #2: Wed Jan 30 16:21:05 CET 2009 c@cb.vu:/usr/obj/usr/src/sys/CB  i386');}
else if(t.argv[1]=='-v'){t.write('%c(@lightgrey)FreeBSD 7.1-STABLE #2: Wed Jan 30 16:21:05 CET 2009 c@cb.vu:/usr/obj/usr/src/sys/CB');}
else if(t.argv[1]=='-r'){t.write('%c(@lightgrey)7.1-STABLE');}
else{t.write(['%c(@lightgrey)uname: illegal option -'+t.argv[1],'usage: uname [-aimnprsv]']);}}
function cmdHostname(t){if(t.argv.length==1||t.argv[1]=='-f'){t.write('%c(@lightgrey)cb.vu');}
else if(t.argv[1]=='-s'){t.write('%c(@lightgrey)cb');}
else if(t.argv[1]=='-i'){t.write('%c(@lightgrey)78.31.70.238');}
else{t.write(['%c(@lightgrey)uname: illegal option -'+t.argv[1],'usage: hostname [-fsi]']);}}
function cmdReset(t){t.write(' ');t.clear();t.rawMode=true;t.open();return;}
function cmdCat(t,iseditor,filename){var error="ok";if(t.argv.length==1&&!iseditor){t.write('%c(@lightgrey)usage: cat file');return error;}
if(typeof filename=='undefined'){filename=t.argv[1];}
var fpath=getPath(filename);var fname=fpath[1];var lpath=fpath[0];var fullname=fpath[2];var cnt;var tindex=tree.indexOf(lpath);if(tindex==-1){error="Error: "+lpath+" wrong path";return error;}
var findex=tree_files[tindex][0].indexOf(fname);if(findex!=-1||fname=='unixtoolbox.xhtml'){var fcontent=readCookie(fname);if(fcontent){cnt=fcontent.slice(0,fcontent.length-12);cnt=cnt.replace(/~~/g,";");t.write('%c(@lightgrey)'+cnt+'%n');return error;}
cnt=filesContent[fullname];if(typeof cnt!='undefined'&&cnt!='undefined'){t.write(cnt);}else{if(remote_files.indexOf(fname)!=-1){cnt=filesContent[fullname];t.write('%c(@lightgrey)'+cnt+'%n');}else if(binary_files.indexOf(fname)!=-1){if(iseditor){error=" binary file";}else{window.location='http://cb.vu/'+fname;}}else{error=fname+': Permission denied';if(iseditor){return'Open '+error;}else{t.write('%c(@lightgrey)cat : '+error+'%n');}}}}else{if(!iseditor){t.write('%c(@lightgrey)cat : '+fname+' : File not found%n');}else{error="";}}
return error;}
function cmdMan(t){if(t.argv.length==1){t.write('%c(@lightgrey)usage: man <command>%n');t.write('%c(@lightgrey)The following man pages are available, or use apropos on any command.%n');listing(t,files_man[0]);return;}
var cmd=t.argv[1];if(files_man_n.indexOf(cmd)!=-1){var dim=t.getDimensions();var file=filesContent['/usr/share/man/'+cmd];if(file.length>t.conf.rows-1){t.write(file,true);}else{t.write(file);}}
else{t.write('%c(@lightgrey)No manual entry for '+cmd);}}
function cmdMore(t){if(t.argv.length==1){t.write('%c(@lightgrey)usage: more <file>');return;}
var fpath=getPath(t.argv[1]);var fname=fpath[1];var lpath=fpath[0];var fullname=fpath[2];var cnt;var findex;var tindex=tree.indexOf(lpath);if(tindex==-1){return;}
if(lpath=='/home/www'){findex=tree_files[tindex][0].indexOf(fname);if(findex!=-1){t.clear();var fcontent=readCookie(fname);if(fcontent){cnt=fcontent.slice(0,fcontent.length-12);cnt=cnt.replace(/~~/g,";");t.write('%c(@lightgrey)'+cnt+'%n');return;}else if(fname=='sitemap.xml'){t.write(file_sitemap,true);return;}else if(fname=='cb.txt'){t.write(file_cb,true);return;}else if(fname=='about.txt'){t.write(file_about,true);return;}else if(fname=='bugs.txt'){t.write(file_bugs,true);return;}}
if(remote_files.indexOf(fname)!=-1){t.rawMode=true;t.write('%c(@lightgrey)Patience...%n');fetchHttp('http://cb.vu/'+fname,displaymore);}else if(binary_files.indexOf(fname)!=-1){t.write('%c(@lightgrey)Binary file. Use pr instead');}else{t.write('%c(@lightgrey)File not found.');}}else if(lpath=='/etc'){findex=tree_files[tindex][0].indexOf(fname);if(findex!=-1){cnt=filesContent[fullname];if(typeof cnt!='undefined'){t.clear();t.write(cnt);}
else{t.write('%c(@lightgrey)'+fname+' : Permission denied%n');}}else{t.write('%c(@lightgrey)'+fname+' : File not found%n');}}}
function cmdPr(t){if(t.argv.length==1){t.write('%c(@lightgrey)usage: pr file');}
else{window.location=t.argv[1];}}
function cmdRedim(t,manual){var oldie=false;if(typeof document.documentElement.style.maxHeight=="undefined"){oldie=true;}
if(navigator.appVersion.indexOf('Safari')!=-1){safari=true;}
var dim=t.getDimensions();var neww=Math.round((t.conf.cols/dim.width)*browserWidth())-2;var newh=Math.round((t.conf.rows/dim.height)*browserHeight())-1;if(oldie){t.write('Using IE6 hack%n');neww=neww-2;}
if((t.argv)&&t.argv.length>1||manual){t.write('Terminal dimentions in px:       '+dim.width+' x '+dim.height+' px%n');t.write('Browser window dimentions in px: '+browserWidth()+' x '+browserHeight()+' px%n');t.write('Terminal columns x rows:         '+t.conf.cols+' x '+t.conf.rows+' char%n');t.write('Maximal columns x rows:          '+neww+' x '+newh+' char%n');}else if(!(t.argv)||t.argv.length==1){if(neww!==0){t.resizeTo(neww,newh);t.maxCols=neww;t.maxLines=newh;if(neww<(6*asciinumber[0].length/10)+(2*asciiddot[0].length)){asciin=asciinumber_s;asciid=asciiddot_s;}else{asciin=asciinumber;asciid=asciiddot;}
numh=asciin.length;numw=asciin[0].length/10;}}}
function redim(){cmdRedim(globalterm);}
function cmdTime(t){var d=new Date();t.write('%c(@lightgrey)'+d.milTime());}
function displayNum(str,center){var n=0;for(var i=0;i<numh;i++){asciistr[i]='';}
for(var k=0;k<str.length;k++){if(str.charAt(k)==':'){for(var j=0;j<numh;j++){asciistr[j]=asciistr[j]+asciid[j];}}else{n=str.charAt(k);for(var l=0;l<numh;l++){asciistr[l]=asciistr[l]+asciin[l].slice(n*numw,(n*numw)+numw);}}}
if(!center){globalterm.write(s);}
else{var r=Math.round(globalterm.conf.rows/2)-Math.round(numh/2);var c=Math.round((globalterm.conf.cols-asciistr[0].length)/2);for(var m=0;m<asciistr.length;m++){globalterm.typeAt(r+m,c,asciistr[m],3*256);}}}
function clockHandler(initterm){if(initterm){initterm.env.handler=initterm.handler;initterm.cursorOff();asciistr=[];numh=asciin.length;numw=asciin[0].length/10;r=Math.round(globalterm.conf.rows/2)-Math.round(numh/2);c=Math.round((globalterm.conf.cols-(6*numw)-2*asciid[0].length)/2)+(4*numw)+2*asciid[0].length;return;}
this.lock=true;var key=this.inputChar;if(key==32){if(interval===0){interval=setInterval('carriageReturn()',1000);}else{clearInterval(interval);interval=0;}}else if(key==114){started=new Date();}else if(key!=globalterm.termKey.CR){clearInterval(interval);interval=0;clockvisible=false;stopwatch=false;this.charMode=false;this.handler=this.env.handler;this.clear();this.prompt();return;}else{now=new Date();var h;var m;var s;if(!stopwatch){h=now.getHours();m=now.getMinutes();s=now.getSeconds();}else{var diff=(now-started)/1000;s=Math.floor(diff%60);diff=diff/60;m=Math.floor(diff%60);diff=diff/60;h=Math.floor(diff%24);}
var sh=''+h;var sm=''+m;var ss=''+s;if(h<10){sh='0'+h;}
if(m<10){sm='0'+m;}
if(s<10){ss='0'+s;}
if(!clockvisible){displayNum(sh+':'+sm+':'+ss,true);clockvisible=true;}else if(s<2){displayNum(sh+':'+sm+':'+ss,true);}
else{for(var j=0;j<numh;j++){asciistr[j]=''+
asciin[j].slice(ss.charAt(0)*numw,(ss.charAt(0)*numw)+numw)+
asciin[j].slice(ss.charAt(1)*numw,(ss.charAt(1)*numw)+numw);}
for(var i=0;i<asciistr.length;i++){this.typeAt(r+i,c,asciistr[i],3*256);}}}
this.lock=false;}
function cmdClock(t){started=new Date();var findex=-1;if(t.argv.length>=2){findex=t.argv.indexOf('-t');}
if(findex!=-1){stopwatch=true;t.argv.splice(findex,1);findex=-1;}
if(t.argv.length>=2){findex=t.argv.indexOf('-s');}
if(findex!=-1){asciin=asciinumber_s;asciid=asciiddot_s;t.argv.splice(findex,1);findex=-1;}else if(t.conf.cols>(6*asciinumber[0].length/10)+(2*asciiddot[0].length)){asciin=asciinumber;asciid=asciiddot;}
t.charMode=true;t.cursorOff();t.clear();clockHandler(t);interval=setInterval('carriageReturn()',1000);t.handler=clockHandler;t.lock=false;return;}
function setNormal(){term.conf.bgColor='#181818';term.rebuild();}
function setColor(color){term.conf.bgColor=color;term.rebuild();globalterm.write(' ');}
var bootline=0;var booting=false;var sdnotifed=false;var rebootask1=false;var rebootask2=false;var rebootask3=false;var beenhere=false;function rebootHandler(initterm){if(initterm){initterm.env.handler=initterm.handler;if(beenhere){initterm.write('%c(@orange)You again?? %c(@lightgrey)Alright you want to reboot, but are you sure?');}else{initterm.write('%c(@lightgrey)So you want to reboot. Are you sure?');}
return;}
this.newLine();this.charMode=false;this.lock=true;if(this.isPrintable(key)){var ch=String.fromCharCode(key);this.type(ch);}
if(!rebootask1){beenhere=true;this.cursorOn();if(this.lineBuffer=='yes'){rebootask1=true;this.write('%c(@lightgrey)Are you really sure you know which machine is actually going to reboot?');this.newLine();}else if(this.lineBuffer=='no'){this.write('%c(@lightgrey)Good choice! Go play with the other commands.');this.charMode=false;this.handler=this.env.handler;this.prompt();return;}else if(this.lineBuffer){this.write('%c(@lightgrey)answer yes or no');this.newLine();}
this.lock=false;this.lineBuffer='';return;}else if(!rebootask2){this.cursorOn();if(this.lineBuffer=='yes'){rebootask2=true;this.write('%c(@lightgrey)So will that be yours or mine? Answer "yours" or "mine" or "quit"');this.newLine();}else if(this.lineBuffer=='no'){this.write('%c(@lightgrey)I don\'t know either :o)');rebootask1=false;this.charMode=false;this.handler=this.env.handler;this.prompt();return;}else if(this.lineBuffer){this.write('%c(@lightgrey)answer with yes or no');this.newLine();}
this.lock=false;this.lineBuffer='';return;}else if(!rebootask3){this.cursorOn();if(this.lineBuffer=='yours'||this.lineBuffer=='mine'){this.cursorOff();rebootask3=true;this.write('%c(@lightgrey)So you mean yours....OK you asked for it.');setTimeout('carriageReturn()',2000);}else if(this.lineBuffer=='quit'){rebootask1=false;rebootask2=false;rebootask3=false;this.write('%c(@lightgrey)whimp :o).');this.charMode=false;this.handler=this.env.handler;this.prompt();return;}else if(this.lineBuffer){this.write('Answer "yours" or "mine" or "quit"');this.newLine();}
this.lock=false;this.lineBuffer='';return;}
this.lock=true;var key=this.inputChar;this.cursorOff();if(key==32){if(interval===0){interval=setInterval('carriageReturn()',300);}else{clearInterval(interval);interval=0;}}else if(!sdnotifed&&!booting){this.newLine();this.write(['%n%n%n%c(@orange)Shutdown at '+Date(),'%c(@chartreuse)shutdown: [pid '+randomRange(189,21000)+']','root:{'+randomRange(70,150)+'}'+path,'%n%n*** System shutdown message from '+clientip+' ***','Sytem going down in 4 seconds%c()%n%n%n']);this.write('Send SIGTERM to all processes%n');this.write(pslong);this.newLine();this.write('%n%n');sdnotifed=true;setTimeout('interval = setInterval (\'carriageReturn()\', 300 )',4000);}else if(!booting&&sdnotifed){if(bootline==filesContent['/boot/shutdown'].length-1){bootline=0;clearInterval(interval);interval=0;this.clear();term.conf.bgColor='blue';term.rebuild();this.write(' ');booting=true;setTimeout('setColor(\'#ffffff\')',1200);setTimeout('setNormal()',1600);setTimeout('interval = setInterval (\'carriageReturn()\', 300 )',2500);}else{this.write(filesContent['/boot/shutdown'][bootline]);bootline++;if(bootline==filesContent['/boot/shutdown'].length-1){clearInterval(interval);interval=setInterval('carriageReturn()',4000);}}}else{if(bootline==filesContent['/boot/kernel'].length-1){clearInterval(interval);interval=0;bootline=0;booting=false;rebootask1=false;rebootask2=false;this.newLine();cmdRedim(this,true);this.charMode=false;this.handler=this.env.handler;this.cursorOn();setTimeout('globalterm.clear()',3000);setTimeout('location.reload()',3500);}else{this.write(filesContent['/boot/kernel'][bootline]);bootline++;}}
this.lock=false;}
function cmdReboot(t){if(t.user!='root'){t.write("%c(@lightgrey)You must be root to do this");return;}
t.charMode=true;rebootHandler(t);t.handler=rebootHandler;setTimeout('carriageReturn()',100);t.lock=false;return;}
function cmdNum(t){if(t.argv.length==1){t.write('%c(@lightgrey)usage: num number');}
asciistr=[];displayNum(t.argv[1],true);}
function randomScreen(isgame){globalterm.wrap=true;var maxr=0;allRows=[];globalterm.clear();if(typeof isgame=='undefined'){isgame=false;}
if(isgame){maxr=globalterm.conf.rows-2;}else{maxr=globalterm.conf.rows;}
firstline="";for(var j=0;j<=maxr;j++){for(var i=0;i<globalterm.conf.cols;i++){if(isgame){if(i===0||i==globalterm.conf.cols-1){firstline+='*';continue;}
if(randomRange(1,250)<=snakefood){firstline+=randomRange(1,9);}else{firstline+=' ';}}else{firstline+=String.fromCharCode(randomRange(38,126));}}
allRows.push(firstline);firstline="";}
if(isgame){for(var k=0;k<globalterm.conf.cols;k++){firstline+='%c(@lightgrey)*';}
allRows[0]=allRows[maxr]=firstline;var nrocks=Math.round((globalterm.conf.cols*globalterm.conf.rows)/snakerocks);for(var rocks=0;rocks<nrocks;rocks++){var rockr=randomRange(2,globalterm.conf.rows-9);var rockc=randomRange(2,globalterm.conf.cols-7);var rockchar='';for(var kr=0;kr<4;kr++){if(kr>0&&kr<3){rockchar="#####";}
else if(kr===0){rockchar=",###,";}
else if(kr==3){rockchar="'###'";}
allRows[rockr+kr]=allRows[rockr+kr].slice(0,rockc)+
rockchar+allRows[rockr+kr].slice(rockc+5);}}
for(var l=0;l<allRows.length;l++){allRows[l]=allRows[l].replace(/#/g,"%c(@orange)#%c(@lightgrey)");allRows[l]=allRows[l].replace(/,/g,"%c(@orange),%c(@lightgrey)");allRows[l]=allRows[l].replace(/\'/g,"%c(@orange)'%c(@lightgrey)");}}
return allRows;}
function cmdRandom(t){if(t.argv.length==2&&t.argv[1]=='n'){t.write(randomScreen(true));}else{t.write(randomScreen());}}
function iterateArray(write){if(Math.round(Math.random()-0.40)===0){s=' ';}
else{s='1';}
for(i=0;i<mcolors.length;i++){if(i===0){repl=s;}
else{repl='%c(#'+mcolors[i-1]+')'+sp.charAt(randomRange(0,61));}
firstline=firstline.replace(regex[i],repl);}
repl='%c(#fff)'+sp.charAt(randomRange(0,61));for(i=0;i<xperIter;i++){var p=randomRange(0,firstline.length);if(firstline.charAt(p)==' '||firstline.charAt(p)=='1'){firstline=firstline.slice(0,p)+'%c(#fff)#'+firstline.slice(p+1);}}
var rowtochange=randomRange(1,allRows.length-1);allRows[rowtochange]=allRows[rowtochange].replace(regex[mcolors.length],repl);allRows.unshift(firstline);allRows.pop();if(write){globalterm.write(allRows);}}
var matrixrounds;var matrixemptystart=false;function matrixHandler(initterm){var repl;var i=0;if(initterm){if(initterm.argv.indexOf('-s')!=-1){matrixemptystart=true;}else{matrixemptystart=false;}
matrixrounds=globalterm.conf.rows*3;firstline='';xperIter=Math.round(globalterm.conf.cols/45);initterm.env.handler=initterm.handler;dim=initterm.getDimensions();for(i=0;i<mcolors.length;i++){regex[i]=eval('\/%c\\(#'+mcolors[i]+'\\).\/g');}
regex[mcolors.length]=eval('\/%c\\(#fff\\).\/g');for(i=0;i<initterm.conf.cols-1;i++){firstline=firstline+' ';}
for(i=0;i<=initterm.conf.rows;i++){allRows[i]=firstline;}
return;}
this.lock=true;var key=this.inputChar;if(key==32){if(interval===0){interval=setInterval('carriageReturn()',1000);}else{clearInterval(interval);interval=0;}}
if(key==113||key==termKey.ESC){clearInterval(interval);interval=0;this.charMode=false;this.handler=this.env.handler;this.clear();this.prompt();return;}else{if(!matrixemptystart&&matrixrounds>0){while(matrixrounds>0){iterateArray(false);matrixrounds--;}}
iterateArray(false);this.write(allRows);}
this.lock=false;}
function cmdMatrix(t){t.cursorOff();t.charMode=true;t.write('%c(@lightgrey)Use q or ESC to quit. Space to pause%n');t.write('%c(@lightgrey)  -s for empty start%n');t.write('%c(@lightgrey)  See also man matrix%n');matrixHandler(t);interval=setInterval('carriageReturn()',1200);t.handler=matrixHandler;t.lock=false;return;}
function init(t){var numproc=randomRange(9,12);var h=randomRange(0,9);var m=randomRange(10,59);for(var i=0;i<numproc;i++){var s=randomRange(10,59);pslong.push('%c(@lightgrey)www  '+randomRange(1000,5100)+'  0.0  1.5 '+
randomRange(10000,51000)+' '+randomRange(10000,51000)+' ??  I      '+h+':'+m+'.'+s+' /usr/local/sbin/httpd');}}
function cmdPs(t){var numproc=randomRange(8,14);var h=randomRange(0,9);var m=randomRange(10,59);if(t.argv.length==1){t.write('%c(@lightgrey)PID  TT  STAT      TIME COMMAND%n');for(var i=0;i<numproc;i++){var s=((i*13)+10)%60;s=(s<10)?10:s;t.write('%c(@lightgrey)'+randomRange(1000,5100)+' ??  I      '+h+':'+m+'.'+s+' /usr/local/sbin/httpd%n');}}else{t.write(pslong);}}
function cmdFortune(t){t.write(varfortune[fortuneid]);fortuneid++;fortuneid=fortuneid%3;if(fortuneid===0){fetchHttp("http://cb.vu/fortune.js.php",eval);}}
function cmdWhatis(t){if(t.argv.length==1){t.write('%c(@lightgrey)usage: whatis/apropos <command>');}
else if(t.argv[1]=='help'){t.write('%c(@lightgrey)display the help message');}
else if(t.argv[1]=='info'){t.write('%c(@lightgrey)display the info message with credentials');}
else if(t.argv[1]=='clear'){t.write('%c(@lightgrey)clear the terminal');}
else if(t.argv[1]=='echo'){t.write('%c(@lightgrey)echo the arguments or create a file with >');}
else if(t.argv[1]=='ls'||t.argv[1]=='ll'){t.write('%c(@lightgrey)list directory contents');}
else if(t.argv[1]=='cd'){t.write('%c(@lightgrey)change working directory');}
else if(t.argv[1]=='rm'){t.write('%c(@lightgrey)delete a file mostly for root only');}
else if(t.argv[1]=='uname'){t.write('%c(@lightgrey)display system identification');}
else if(t.argv[1]=='whoami'){t.write('%c(@lightgrey)display effective user id');}
else if(t.argv[1]=='whereami'){t.write('%c(@lightgrey)display you probable position with country and city');}
else if(t.argv[1]=='weather'){t.write('%c(@lightgrey)display weather information based on your location');}
else if(t.argv[1]=='who'){t.write('%c(@lightgrey)display who is on the system');}
else if(t.argv[1]=='id'){t.write('%c(@lightgrey)return user identity');}
else if(t.argv[1]=='matrix'){t.write('%c(@lightgrey)show a matrix like screen saver (it is CPU hungry)');}
else if(t.argv[1]=='more'){t.write('%c(@lightgrey)display a file with paging function');}
else if(t.argv[1]=='pwd'){t.write('%c(@lightgrey)return working directory name');}
else if(t.argv[1]=='cat'){t.write('%c(@lightgrey)concatenate and print files');}
else if(t.argv[1]=='chat'){t.write('%c(@lightgrey)chat with the terminal chatbot');}
else if(t.argv[1]=='hostname'){t.write('%c(@lightgrey)set or print name of current host system');}
else if(t.argv[1]=='ps'){t.write('%c(@lightgrey)process status');}
else if(t.argv[1]=='pr'){t.write('%c(@lightgrey)print files on the browser');}
else if(t.argv[1]=='browse'){t.write('%c(@lightgrey)display the file on the browser');}
else if(t.argv[1]=='browser'){t.write('%c(@lightgrey)display your IP address and browser information');}
else if(t.argv[1]=='cal'){t.write('%c(@lightgrey)displays a calendar');}
else if(t.argv[1]=='uptime'){t.write('%c(@lightgrey)show how long system has been running');}
else if(t.argv[1]=='date'){t.write('%c(@lightgrey)display date and time');}
else if(t.argv[1]=='time'){t.write('%c(@lightgrey)time command execution');}
else if(t.argv[1]=='clock'){t.write('%c(@lightgrey)display a full screen clock or stopwatch with the option -t');}
else if(t.argv[1]=='top'){t.write('%c(@lightgrey)display information about the top cpu processes');}
else if(t.argv[1]=='df'){t.write('%c(@lightgrey)display free disk space');}
else if(t.argv[1]=='history'){t.write('%c(@lightgrey)display the last used commands');}
else if(t.argv[1]=='fortune'){t.write('%c(@lightgrey)print a random, hopefully interesting, adage');}
else if(t.argv[1]=='su'){t.write('%c(@lightgrey)substitute user identity');}
else if(t.argv[1]=='ssh'){t.write('%c(@lightgrey)ssh connection using the mindterm java terminal');}
else if(t.argv[1]=='vi'){t.write('%c(@lightgrey)vi the editor!');}
else if(t.argv[1]=='snake'){t.write('%c(@lightgrey)A variation of the classical snake game');}
else if(t.argv[1]=='invaders'){t.write('%c(@lightgrey)The invaders game provided by Norbert Landsteiner');}
else if(t.argv[1]=='logout'||t.argv[1]=='exit'){t.write('%c(@lightgrey)Exit and logout from the terminal');}
else if(t.argv[1]=='reset'){t.write('%c(@lightgrey)reset the terminal as it\'s initial state');}
else if(t.argv[1]=='reload'){t.write('%c(@lightgrey)reload the web page');}
else if(t.argv[1]=='ping'){t.write('%c(@lightgrey)ping a host, or yourself when no argument is given');}
else{t.write('%c(@lightgrey)'+t.argv[1]+': nothing appropriate');}}
var viquit=false;var visave=false;var viforce=false;var viopen=false;var viedit=false;var visaved=true;var visplvis=false;var vicmd="";var vifile="";function readOneLine(t,row){var c=0;var line="";while(t.isPrintable(t.charBuf[row][c])&&c<t.maxCols){line+=String.fromCharCode(t.charBuf[row][c]);c++;}
return line;}
function removeLine(t,row){var l=0;var content="";for(var r=row;r<t.maxLines-1;r++){content=readOneLine(t,r+1);t.typeAt(r,0,content);t.c=content.length;t.r=r;while(t.isPrintable(t.charBuf[r][t.c])){t.fwdDelete();}
l++;if(t.charBuf[r][0]==126){break;}}
t.c=0;t.r=row;}
function viSplash(t){var splash=['                    Vi','','           version 0.1 alpha :o)','','   <ESC>        to enter command mode','   :q<Enter>    to exit','   :w<Enter>    to save','   :w filename  to save to "filename"','   :e filename  to open "filename"','   :q!<Enter>   to exit without saving','   D            to delete rest of line','   dd           to delete current line','   x            to delete current char','   i            to enter edit mode    ','   UP RIGHT DOWN LEFT to move the cursor','   or h left  j down  k up  l right','','Paging is not possible, sorry. Only one','window (or page) can be edited at a time.','','both vi *and* Emacs are just too damn slow','Use ED! See man ed'];if(safari){splash.push('On Safari browsers use <TAB> instead of <ESC>');}
visplvis=true;centerSplash(t,splash);}
function centerSplash(t,splash){var sh=splash.length;var sw=0;for(var i=0;i<sh;i++){if(splash[i].length>sw){sw=splash[i].length;}}
var r=Math.round(t.conf.rows/2)-Math.round(sh/2)-3;var c=Math.round(t.conf.cols/2)-Math.round(sw/2);if(r<0){r=0;}
for(var m=0;m<sh;m++){if(m<16){t.typeAt(r+m,c,splash[m],7*256);}
else{t.typeAt(r+m,c,splash[m],5*256);}}}
function saveFile(t,fname){var content="";for(var r=0;r<t.maxLines-1;r++){if(t.charBuf[r][0]!=126){content+=readOneLine(t,r)+'%n';}}
content=content.slice(0,content.length-2);var error=addFile(fname,content,true);if(error===""&&typeof error!='undefined'){t.statusLine("File saved to "+fname);return true;}else{t.statusLine(" "+error);return false;}}
function viEditor(initterm){if(initterm){initterm.clear();initterm.maxLines=globalterm.conf.rows-1;initterm.env.mode='ctrl';initterm.env.handler=initterm.handler;var error="";if(vifile!==""){error=cmdCat(initterm,true);if(error===""){initterm.statusLine("\""+vifile+"\" [New File]");viSplash(initterm);}else if(error!="ok"&&typeof error!='undefined'){initterm.statusLine("Error: "+error,1);}else{initterm.write('%n');if(safari){initterm.statusLine(' On Safari browsers use <TAB> instead of <ESC>');}}}else{initterm.statusLine(" [New File]");viSplash(initterm);}
if(!visplvis){for(var r=initterm.r;r<initterm.maxLines;r++){initterm.printRowFromString(r,'~');}}
return;}
this.lock=true;this.cursorOff();var key=this.inputChar;if(key==termKey.LEFT){this.cursorLeft();}
else if(key==termKey.RIGHT){this.cursorRight();}
else if(key==termKey.UP){var c=this.c;var ru=this.r-1;if(ru<0){ru=0;}
while(!this.isPrintable(this.charBuf[ru][c])&&c>0){c--;}
this.cursorSet(ru,c);}
else if(key==termKey.DOWN){var cd=this.c;var rd=this.r+1;if(this.charBuf[rd][0]!=126){while(!this.isPrintable(this.charBuf[rd][cd])&&cd>0){cd--;}
this.cursorSet(rd,cd);}}
if(visplvis){for(var ro=this.r;ro<this.maxLines;ro++){this.printRowFromString(ro,'~');}
visplvis=false;}
if(this.env.mode=='ctrl'){if(key==104&&vicmd.charAt(0)!=':'){this.cursorLeft();}
else if(key==106&&vicmd.charAt(0)!=':'){var cd2=this.c;var rd2=this.r+1;if(this.charBuf[rd2][0]!=126){while(!this.isPrintable(this.charBuf[rd2][cd2])&&cd2>0){cd2--;}
this.cursorSet(rd2,cd2);}}
else if(key==107&&vicmd.charAt(0)!=':'){var c2=this.c;var ru2=this.r-1;if(ru2<0){ru2=0;}
while(!this.isPrintable(this.charBuf[ru2][c2])&&c2>0){c2--;}
this.cursorSet(ru2,c2);}
else if(key==108&&vicmd.charAt(0)!=':'){this.cursorRight();}
if(key==termKey.CR){if(vicmd.charAt(0)!=':'){viquit=viopen=visave=viforce=viedit=false;vicmd="";this.statusLine("Error: no command given. Use <ESC>:q to quit.");}
if(visave){viopen=visave=viedit=false;var name=vicmd.split(' ');if(name.length>2){this.statusLine("Error: no space in file name. Use <ESC>:w filename.");}else if(name.length==2||vifile!==""){if(name.length==2){vifile=name[1];}
if(saveFile(this,vifile)){visaved=true;}else{viquit=false;}}else{this.statusLine("Error: no file name. Use <ESC>:w filename to save.");}}else if(viopen){viquit=viopen=visave=viedit=false;var fname=vicmd.split(' ');if(fname.length>2){this.statusLine("Error: no space in file name. Use <ESC>:e filename.");}else if(fname.length==2||vifile!==""){if(fname.length==2){vifile=fname[1];}}
this.clear();error=cmdCat(this,true,vifile);if(error===""){this.statusLine("\""+vifile+"\" [New File]");}else if(error!="ok"&&typeof error!='undefined'){this.statusLine("Error: "+error,1);}}
if(viquit){if(visaved||viforce){viquit=viopen=visave=viforce=viedit=false;vicmd="";vifile="";this.charMode=false;this.handler=this.env.handler;this.maxLines=globalterm.conf.rows;this.clear();this.prompt();return;}else{this.statusLine("Error: file modified since last write; save or use ! to override.");}}
vicmd="";viquit=viopen=visave=viforce=viedit=false;}else if(key==33&&vicmd.charAt(0)==':'){viforce=true;vicmd+='!';this.statusLine(vicmd);}else if(key==58&&vicmd.charAt(0)!=':'){vicmd+=':';viquit=false;visave=false;viforce=false;this.statusLine(vicmd);}else if(key==113&&vicmd.charAt(0)==':'&&!viedit){viquit=true;vicmd+='q';this.statusLine(vicmd);}else if(key==119&&vicmd.charAt(0)==':'&&!viedit){visave=true;vicmd+='w';this.statusLine(vicmd);}else if(key==101&&vicmd.charAt(0)==':'){viopen=true;vicmd+='e';this.statusLine(vicmd);}else if(key==120&&vicmd.charAt(0)==':'&&!viedit){viquit=visave=true;vicmd+='x';this.statusLine(vicmd);}else if(key==120&&!viedit){visaved=false;this.fwdDelete();}else if(key==68&&!viedit){visaved=false;while(this.isPrintable(this.charBuf[this.r][this.c])){this.fwdDelete();}}else if(key==100&&!viedit){vicmd+="d";if(vicmd=="dd"){visaved=false;this.cursorSet(this.r,0);while(this.isPrintable(this.charBuf[this.r][this.c])){this.fwdDelete();}
removeLine(this,this.r);vicmd="";}}else if(key==105&&!viedit){this.statusLine("-- INSERT --",0);this.env.mode='';}else if(key==termKey.ESC||key==9){this.statusLine("",0);viquit=false;visave=false;viforce=false;vicmd="";}else if(visave||viopen){if(key==32){viedit=true;}
var ch=String.fromCharCode(key);vicmd+=ch;this.statusLine(vicmd);}}
else{if(key==termKey.ESC||key==9){vicmd="";this.statusLine("",1);this.env.mode='ctrl';}
else if(key==termKey.CR){visaved=false;if(!this.isPrintable(this.charBuf[this.r][0])||this.charBuf[this.r][0]==126){this.write(' ');}
if(this.r<this.maxLines-1){this.newLine();}else{this.statusLine("Error: paging not possible. Sorry.");}}
else if(key==termKey.BS){visaved=false;this.backspace();}
else if(key==termKey.DEL){visaved=false;if(this.c===0&&!this.isPrintable(this.charBuf[this.r][0])){removeLine(this,this.r);}else{this.fwdDelete();}}
else if(this.isPrintable(key)){var cha=String.fromCharCode(key);this.type(cha);visaved=false;}}
var sline=readOneLine(this,this.maxLines);sline=sline.slice(0,45);var sp=' ';if(sline.charAt(0)!='E'){for(var j=sline.length;j<this.maxCols-18;j++){sp+=' ';}
this.statusLine(sline+sp+"row:"+this.r+"  col:"+this.c);}
this.lock=false;this.cursorOn();}
function cmdEdit(t){if(t.argv.length==2){vifile=t.argv[1];}
viEditor(t);t.handler=viEditor;t.charMode=true;t.cursorOn();t.lock=false;return;}
var snakedir=1;var snsplvis=false;var snalive=true;var snakespeed=30;var snakefood=3;var snakerocks=700;var snakespeedid=1;var snakefoodid=1;var snakerocksid=1;var snauto=false;var snrestart=false;var snautostr="";var snrestartstr="";var sn;function snakeSplash(t){var splash=['              ~~~ S N A K E ~~~','','             first version alpha...','','<ESC> or q   quit at any time','<space>      pause/play','s            change speed (slow medium fast)','f            change amount of food (numbers)','o            change amount of obstacles (rocks)','a            toggle drunk autopilot on or off','r            toggle auto restart on or off','','Press <Enter> to start the game'];var dummy=[];var emptystr="";for(var j=0;j<51;j++){emptystr+=" ";}
for(var i=0;i<splash.length+3;i++){dummy.push(emptystr);}
snsplvis=true;t.write(randomScreen());centerSplash(t,dummy);centerSplash(t,splash);}
function distToCenter(t,row,col){var c=t.maxCols/2;var r=t.maxLines/2;var d=Math.sqrt(Math.pow(row-r,2)+Math.pow(col-c,2));return d;}
function delta(row,col,row1,col1){var d=Math.sqrt(Math.pow(row-row1,2)+Math.pow(col-col1,2));return d;}
function Snake(initterm){this.t=initterm;this.col=10;this.row=10;this.autoaction=50;this.body=[];this.body.unshift([this.col,this.row]);this.energy=3;this.speedid=snakespeedid;this.foodid=snakefoodid;this.rocksid=snakerocksid;this.speed=[170,100,50];this.food=[1,5,15];this.rocks=[1500,700,300];this.nleft=-2;this.nright=2;this.speedstr=['slow','medium','fast'];this.foodstr=['little','medium','max'];this.rocksstr=['little','medium','max'];snalive=true;snakedir=1;mm=0;snakespeed=this.speed[this.speedid];snakefood=this.food[this.foodid];snakerocks=this.rocks[this.rocksid];}
Snake.prototype.toggleSpeed=function(){this.speedid++;this.speedid=this.speedid%3;snakespeedid=this.speedid;snakespeed=this.speed[sn.speedid];};Snake.prototype.toggleFood=function(){this.foodid++;this.foodid=this.foodid%3;snakefoodid=this.foodid;snakefood=this.food[this.foodid];};Snake.prototype.toggleRocks=function(){this.rocksid++;this.rocksid=this.rocksid%3;snakerocksid=this.rocksid;snakerocks=this.rocks[this.rocksid];};Snake.prototype.eat=function(row,col){if(this.checkFood(row,col)){var e=String.fromCharCode(this.t.charBuf[row][col]);e++;this.energy=this.energy+e-1;}};Snake.prototype.newCoord=function(dir,row,col){switch(dir){case 0:row--;break;case 1:col++;break;case 2:row++;break;case 3:col--;break;default:break;}
return[row,col];};Snake.prototype.turnedLeft=function(left){if(left){if(this.nleft!==0){this.nleft++;}
if(this.nright!=3){this.nright++;}}else{if(this.nleft!=-3){this.nleft--;}
if(this.nright!==0){this.nright--;}}};var lastlooptmp=0;Snake.prototype.snakeAutopilot=function(){var nc=this.body[0][0];var nr=this.body[0][1];var nccand=nc;var nrcand=nr;var dir=snakedir;var rand=0;var leftt=false;var res=[];var m=6;var mcorr=0;if(nc<m||this.t.maxCols-nc<m||nr<m||this.t.maxLines-nr<m){dir=dir+3;dir=dir%4;res=this.newCoord(dir,nr,nc);var nrl=res[0];var ncl=res[1];dir=dir+2;dir=dir%4;res=this.newCoord(dir,nr,nc);var nrr=res[0];var ncr=res[1];if(distToCenter(this.t,nrl,ncl)<distToCenter(this.t,nrr,ncr)){this.mm=mcorr=-1;}else{this.mm=mcorr=1;}}else{this.mm=mcorr=0;}
dir=snakedir;res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(!this.check(nrcand,nccand)){var rnow=nr;var cnow=nc;var rcrash=nrcand;var ccrash=nccand;var loopsteps=0;var stepsright=0;var originaldir=dir;var dirtotake=dir;var deltatocrash=750;var thisdelta=0;var washere=false;var chashchar=this.t.charBuf[rcrash][ccrash];var lastpath=[];dir=dir+2;dir=dir%4;res=this.newCoord(dir,rnow,cnow);nrcand=res[0];nccand=res[1];if(this.t.charBuf[rcrash][ccrash]==79||this.t.charBuf[rcrash][ccrash]==42){while(nrcand!=rcrash||nccand!=ccrash){loopsteps++;dir=dir+3;dir=dir%4;res=this.newCoord(dir,rnow,cnow);nrcand=res[0];nccand=res[1];if(nrcand>=0&&nrcand<=this.t.conf.rows-2&&nccand>=0&&nccand<=this.t.conf.cols-1){if(this.t.charBuf[nrcand][nccand]==79||this.t.charBuf[nrcand][nccand]==42){washere=false;for(var il=0;il<lastpath.length;il++){if(nrcand==lastpath[il][0]&&nccand==lastpath[il][1]){washere=true;break;}}
if(!washere){deltatocrash=delta(nrcand,nccand,rcrash,ccrash);dirtotake=dir;}}}
dir++;dir=dir%4;res=this.newCoord(dir,rnow,cnow);nrcand=res[0];nccand=res[1];if(nrcand>=0&&nrcand<=this.t.conf.rows-2&&nccand>=0&&nccand<=this.t.conf.cols-1){if(this.t.charBuf[nrcand][nccand]==79||this.t.charBuf[nrcand][nccand]==42){washere=false;for(var is=0;is<lastpath.length;is++){if(nrcand==lastpath[is][0]&&nccand==lastpath[is][1]){washere=true;break;}}
if(!washere){thisdelta=delta(nrcand,nccand,rcrash,ccrash);if(thisdelta<deltatocrash){dirtotake=dir;deltatocrash=thisdelta;}}}}
dir++;dir=dir%4;res=this.newCoord(dir,rnow,cnow);nrcand=res[0];nccand=res[1];if(nrcand>=0&&nrcand<=this.t.conf.rows-2&&nccand>=0&&nccand<=this.t.conf.cols-1){if(this.t.charBuf[nrcand][nccand]==79||this.t.charBuf[nrcand][nccand]==42){washere=false;for(var ir=0;ir<lastpath.length;ir++){if(nrcand==lastpath[ir][0]&&nccand==lastpath[ir][1]){washere=true;break;}}
if(!washere){thisdelta=delta(nrcand,nccand,rcrash,ccrash);if(thisdelta<deltatocrash){dirtotake=dir;deltatocrash=thisdelta;}}}}
if(deltatocrash<750){dir=dirtotake;res=this.newCoord(dir,rnow,cnow);nrcand=res[0];nccand=res[1];rnow=res[0];cnow=res[1];lastpath.unshift(res);deltatocrash=750;}else{loopsteps=0;deltatocrash=750;break;}}}
lastlooptmp=loopsteps;if(loopsteps>0&&Math.abs(originaldir-dir)==2){loopsteps=0;}
if(loopsteps>0){res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(!this.check(nrcand,nccand)){dir=dir+2;dir=dir%4;}
originaldir--;originaldir=originaldir%4;if(originaldir==dir){leftt=true;}else{leftt=false;}
this.turnedLeft(leftt);snakedir=dir;}else{dir=snakedir;rand=randomRange(this.nleft+mcorr,this.nright+mcorr);if(rand<0||this.nright===0){dir=dir+3;leftt=true;}else{dir++;}
dir=dir%4;res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(!this.check(nrcand,nccand)){dir=dir+2;dir=dir%4;leftt=(leftt)?false:true;}
snakedir=dir;this.turnedLeft(leftt);this.autoaction++;}}
dir=snakedir;if(this.autoaction<=0){rand=randomRange(this.nleft+mcorr,this.nright+mcorr);if(rand<0||this.nright===0){dir=dir+3;leftt=true;}else{dir++;}
dir=dir%4;res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(!this.check(nrcand,nccand)){dir=dir+2;dir=dir%4;leftt=(leftt)?false:true;res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(this.check(nrcand,nccand)){snakedir=dir;this.turnedLeft(leftt);}}else{snakedir=dir;this.turnedLeft(leftt);}
this.autoaction=randomRange(10,this.t.conf.cols);}
dir=snakedir;res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(!this.checkFood(nrcand,nccand)){dir=dir+3;dir=dir%4;res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(!this.checkFood(nrcand,nccand)){dir=dir+2;dir=dir%4;res=this.newCoord(dir,nr,nc);nrcand=res[0];nccand=res[1];if(this.checkFood(nrcand,nccand)&&this.nright>0){snakedir=dir;this.turnedLeft(false);this.autoaction++;}}else{if(this.nleft<0){snakedir=dir;this.turnedLeft(true);this.autoaction++;}}}
this.autoaction--;};Snake.prototype.check=function(row,col){var ch=String.fromCharCode(this.t.charBuf[row][col]);if(ch!='*'&&ch!='O'&&ch!='#'&&ch!=','&&ch!='\''){return true;}else{return false;}};Snake.prototype.checkFood=function(row,col){var ch=String.fromCharCode(this.t.charBuf[row][col]);if(isnumeric(ch)){return true;}else{return false;}};Snake.prototype.step=function(dir){var nc=this.body[0][0];var nr=this.body[0][1];var res=this.newCoord(dir,nr,nc);nr=res[0];nc=res[1];this.col=nc;this.row=nr;this.body.unshift([nc,nr]);if(this.check(nr,nc)){if(this.checkFood(nr,nc)){this.eat(nr,nc);var newf='';newf+=randomRange(1,9);var randr=randomRange(2,this.t.conf.rows-3);var randc=randomRange(2,this.t.conf.cols-2);if(this.t.charBuf[randr][randc]!=35){this.t.typeAt(randr,randc,newf);}}
this.t.typeAt(nr,nc,'O',3*256);}else{clearInterval(interval);interval=0;snalive=false;this.t.typeAt(nr,nc,'@',2*256);snakespeedid=this.speedid;snakefoodid=this.foodid;}
if(this.energy===0){var last=this.body.pop();this.t.typeAt(last[1],last[0],' ');}else{this.energy--;}
return snalive;};function snakeGame(initterm){if(initterm){initterm.clear();initterm.maxLines=globalterm.conf.rows-1;initterm.env.handler=initterm.handler;initterm.cursorOff();sn=new Snake(initterm);snakeSplash(initterm);if(!snauto){snautostr="";}
else{snautostr="# Autopilot! # ";setTimeout('carriageReturn()',2000);}
if(!snrestart){snrestartstr="";}
else{snrestartstr="# Auto restart ";}
return;}
this.lock=true;var key=this.inputChar;if(snsplvis){if(key==termKey.CR||key==110){if(key==110&&!snalive){sn=new Snake(this);}
if(snalive){this.write(randomScreen(true));snsplvis=false;key=32;}}else if(key!=115&&key!=102&&key!=termKey.ESC&&key!=113&&key!=111){key=0;}}
if(key==termKey.LEFT){snakedir=3;}
else if(key==termKey.RIGHT){snakedir=1;}
else if(key==termKey.UP){snakedir=0;}
else if(key==termKey.DOWN){snakedir=2;}
else if(key>=48&&key<=57){sn.eat(String.fromCharCode(key));}else if(key==termKey.CR){if(snauto){sn.snakeAutopilot();}
if(snalive&&!sn.step(snakedir)){var splash=['                 You crashed!','','               G A M E  O V E R','','<ESC> or q   to quit','n            new game','s            change speed (slow medium fast)','f            change amount of food (numbers)','o            change amount of obstacles (rocks)','','Press n to start a new game'];splash.push('');splash.push('Score: '+sn.body.length);snsplvis=true;var dummy=[];var emptystr="";for(var j=0;j<50;j++){emptystr+=" ";}
for(var i=0;i<splash.length+3;i++){dummy.push(emptystr);}
snsplvis=true;centerSplash(this,dummy);centerSplash(this,splash);if(snrestart){setTimeout('pressKey(110)',2000);}}}if(key==32&&snalive){if(interval===0){interval=setInterval('carriageReturn()',snakespeed);}else{clearInterval(interval);interval=0;this.statusLine("Game paused. Use space key to continue",3);}}else if(key==97){snauto=(snauto)?false:true;if(!snauto){snautostr="";}
else{snautostr="# Autopilot # ";}}else if(key==114){snrestart=(snrestart)?false:true;if(!snrestart){snrestartstr="";}
else{snrestartstr="# Auto restart ";}}else if(key==115){sn.toggleSpeed();}else if(key==102){sn.toggleFood();}else if(key==111){sn.toggleRocks();}else if(key==termKey.ESC||key==113){if(interval!==0){clearInterval(interval);interval=0;}
snauto=false;snrestart=false;this.charMode=false;this.handler=this.env.handler;this.maxLines=globalterm.conf.rows;this.clear();this.prompt();return;}
if(interval!==0||snsplvis){this.statusLine(snrestartstr+snautostr+" Speed: "+sn.speedstr[sn.speedid]+" Food: "+sn.foodstr[sn.foodid]+" Rocks: "+sn.rocksstr[sn.rocksid]+"     Score:"+
sn.body.length+" To digest : "+sn.energy);}
this.lock=false;}
function cmdSnake(t){if(t.argv.length>=2){if(t.argv[1]=='-h'||t.argv[1]=='--help'){t.write('%c(@lightgrey)usage: snake [options]%n');t.write('%c(@lightgrey)  -a for automatic start with autopilot engaged%n');t.write('%c(@lightgrey)  -r for auto restart after a crash (makes a screensaver with -a)%n');t.write('%c(@lightgrey)  -s1 for speed: -s1 = slow; -s3 = fast%n');t.write('%c(@lightgrey)  -f1 for food: -f1 = less; -f3 = more%n%n');t.write('%c(@lightgrey)  -o1 for obstacles: -o1 = less; -o3 = more rocks%n%n');t.write('%c(@lightgrey)  for example: snake -a -s2 -f3%n');t.write('%c(@lightgrey)see splash screen or man page for game options%n');return;}
if(t.argv.indexOf('-a')!=-1){snauto=true;}
if(t.argv.indexOf('-r')!=-1){snrestart=true;}
if(t.argv.indexOf('-s1')!=-1){snakespeedid=0;}
if(t.argv.indexOf('-s2')!=-1){snakespeedid=1;}
if(t.argv.indexOf('-s3')!=-1){snakespeedid=2;}
if(t.argv.indexOf('-f1')!=-1){snakefoodid=0;}
if(t.argv.indexOf('-f2')!=-1){snakefoodid=1;}
if(t.argv.indexOf('-f3')!=-1){snakefoodid=2;}
if(t.argv.indexOf('-o1')!=-1){snakerocksid=0;}
if(t.argv.indexOf('-o2')!=-1){snakerocksid=1;}
if(t.argv.indexOf('-o3')!=-1){snakerocksid=2;}}
snakeGame(t);t.handler=snakeGame;t.charMode=true;t.lock=false;return;}
function cmdInvaders(t){var started=false;if(t.argv.length>=2){if(t.argv[1]=='-h'||t.argv[1]=='--help'){t.write('%c(@lightgrey)usage: invaders [-f]%n');t.write('%c(@lightgrey)  the -f option uses full screen%n');t.write('%c(@lightgrey)This game is courtesy of Norbert Landsteiner <http://www.masswerk.at>%n');started=true;}else if(t.argv[1]=='-f'){started=TermlibInvaders.start(t);}}else{started=TermlibInvaders.start(t,80,25);}
if(started){return;}
else{t.write('Sorry, invaders failed. Your browser window might be too small or wrong option');}
t.wrapping=true;}
var vartoptmp;function topHandler(initterm){if(initterm){initterm.clear();initterm.env.handler=initterm.handler;initterm.cursorOff();vartoptmp=vartop;if(vartoptmp.length>initterm.conf.rows){vartoptmp=vartoptmp.slice(0,initterm.conf.rows);}
initterm.write(vartoptmp);interval=setInterval('carriageReturn()',2000);return;}
this.lock=true;var now=new Date();var h=now.getHours();var hup=(h+8)%24;var m=now.getMinutes();var mup=(m+13)%60;var s=now.getSeconds();var sup=(s+45)%60;if(h<10){h='0'+h;}
if(m<10){m='0'+m;}
if(s<10){s='0'+s;}
if(hup<10){hup='0'+hup;}
if(mup<10){mup='0'+mup;}
if(sup<10){sup='0'+sup;}
var timestr='%c(@lightgrey)'+uptimed+'+'+hup+':'+mup+':'+sup+'    '+h+':'+m+':'+s;var key=this.inputChar;if(key==termKey.CR){var procarray=vartoptmp.slice(8,vartop.length-1);procarray.shuffle();this.cursorSet(0,57);this.write(timestr);this.cursorSet(8,0);this.write(procarray);}else{clearInterval(interval);interval=0;this.charMode=false;this.handler=this.env.handler;this.clear();this.prompt();this.wrapping=true;return;}
this.lock=false;}
function cmdTop(t){t.wrapping=false;topHandler(t);t.handler=topHandler;t.charMode=true;t.lock=false;return;}
function cmdSsh(t){t.clear();t.charMode=true;t.cursorOff();t.write("Loading SSH java applet...%n");t.write('Close with the "X" on the top right corner.%n');t.write('See also the man ssh for options.%n');var server='';var username='';var sshport=0;var tunnel='';if(t.argv.length>1){for(var i=1;i<t.argv.length;i++){if(t.argv[i].indexOf('-p')!=-1){sshport=t.argv[i+1];}
if(t.argv[i].indexOf('-L')!=-1){tunnel=t.argv[i+1];}}
if(t.argv[t.argv.length-1].indexOf('@')!=-1){var args=t.argv[t.argv.length-1].split('@');server=args[1];username=args[0];}else{server=t.argv[t.argv.length-1];}}
var dim=t.getDimensions();var sshcol=t.conf.cols;var sshrow=Math.round(t.conf.rows*0.8);var geom=sshcol+'x'+sshrow+'+0-0';var bold=document.createElement('b');var link=document.createElement('a');link.setAttribute("href","javascript:closeSsh()");link.setAttribute("id","link");var text=document.createTextNode("X");bold.appendChild(text);link.appendChild(bold);var applet=document.createElement('applet');applet.setAttribute("id","applet");applet.setAttribute("width",dim.width-10);if(safari){applet.setAttribute("height",dim.height);}else{applet.setAttribute("height",dim.height-9);}
applet.setAttribute("archive","http://cb.vu/mindterm.jar");applet.setAttribute("code","com.mindbright.application.MindTerm.class");var p1=document.createElement("param");p1.setAttribute("name","autoprops");p1.setAttribute("value","both");applet.appendChild(p1);var p2=document.createElement("param");p2.setAttribute("name","term-type");p2.setAttribute("value","xterm-color");applet.appendChild(p2);var p3=document.createElement("param");p3.setAttribute("name","sepframe");p3.setAttribute("value","false");applet.appendChild(p3);var p4=document.createElement("param");p4.setAttribute("name","geometry");p4.setAttribute("value",geom);applet.appendChild(p4);var p5=document.createElement("param");p5.setAttribute("name","scrollbar");p5.setAttribute("value","none");applet.appendChild(p5);var p6=document.createElement("param");p6.setAttribute("name","menus");p6.setAttribute("value","popN");applet.appendChild(p6);var p8=document.createElement("param");p8.setAttribute("name","fg-color");p8.setAttribute("value","94,131,224");applet.appendChild(p8);var p9=document.createElement("param");p9.setAttribute("name","bg-color");p9.setAttribute("value","24,24,24");applet.appendChild(p9);var p13=document.createElement("param");p13.setAttribute("name","font-size");p13.setAttribute("value","13");applet.appendChild(p13);var p14=document.createElement("param");p14.setAttribute("name","local0");p14.setAttribute("value","/general/8080:localhost:80");applet.appendChild(p14);if(username!=''){var p10=document.createElement("param");p10.setAttribute("name","server");p10.setAttribute("value",server);applet.appendChild(p10);var p11=document.createElement("param");p11.setAttribute("name","username");p11.setAttribute("value",username);applet.appendChild(p11);var p12=document.createElement("param");p12.setAttribute("name","quiet");p12.setAttribute("value","true");applet.appendChild(p12);}
if(sshport!==0){var p20=document.createElement("param");p20.setAttribute("name","port");p20.setAttribute("value",sshport);applet.appendChild(p20);}
if(tunnel!==''){var tunnelstr="/general/"+tunnel;var p21=document.createElement("param");p21.setAttribute("name","local1");p21.setAttribute("value",tunnelstr);applet.appendChild(p21);}
document.body.appendChild(link);document.body.appendChild(applet);return;}
function closeSsh(){var applet=document.getElementsByTagName("applet")[0];applet.parentNode.removeChild(applet);var link=document.getElementsByTagName("a")[1];link.parentNode.removeChild(link);globalterm.clear();globalterm.charMode=false;globalterm.lock=false;globalterm.cursorOn();globalterm.prompt();return;}
function bsHandler(initterm){if(initterm){initterm.clear();initterm.env.handler=initterm.handler;initterm.cursorOff();setColor('blue');initterm.write(bs);return;}
this.lock=true;this.charMode=false;this.handler=this.env.handler;this.clear();this.prompt();setNormal();return;}
function cmdBlueScreen(t){t.wrapping=false;bsHandler(t);t.handler=bsHandler;t.charMode=true;t.lock=false;return;}
var userchat="";function chatHandler(initterm){if(initterm){initterm.clear();initterm.env.handler=initterm.handler;initterm.write('%c(@lightgrey)> Hello. How are you today?%n');return;}
this.lock=true;var key=this.inputChar;if(key==35){this.charMode=false;this.handler=this.env.handler;this.clear();this.prompt();fetcherror="";return;}
if(this.isPrintable(key)){var ch=String.fromCharCode(key);this.type(ch);userchat+=ch;}
if(key==termKey.BS){this.backspace();}
else if(key==termKey.CR){if(!botloaded){if(fetcherror.length>0){this.write('%c(@lightgrey)> Sorry no network, could not load my notes. Bye.%n');}else{this.write('%c(@lightgrey)> Wait a few more seconds while I load my notes.%n');}}
this.cursorOff();this.newLine();if(botloaded){doAI(userchat);}
var upstr="";for(var i=0;i<userchat.length;i++){upstr+=userchat.charAt(i).toUpperCase();}
if(upstr=='BYE'||upstr=='GOODBYE'||upstr=='QUIT'||upstr=='EXIT'||upstr=='BYE'){setTimeout('pressKey(35)',2000);}
userchat="";}
this.lock=false;this.cursorOn();}
var botloaded=false;function cmdChat(t){if(!botloaded){fetchHttp("http://cb.vu/bot.js",evaljs);}
chatHandler(t);t.handler=chatHandler;t.charMode=true;t.cursorOn();t.lock=false;return;}
var term=null;function termInitHandler(){this.user='www';globalterm=this;cmdRedim(this);var cookiebroken=readCookie("broken");if(cookiebroken.length>0){this.write('You broke it!');this.charMode=true;this.lock=true;this.cursorOff();return;}
var thislog='%c(@lightgrey)Last login: '+Date()+' from '+clientip;var cookielastlog=readCookie("clilastlog");var oldlog=cookielastlog?cookielastlog:thislog;createCookie("clilastlog",thislog,365);this.write([oldlog,'FreeBSD 7.1-STABLE (CB.VU) #3: '+Date(),'%n%n      ----   Welcome to cb.vu   ----  (start with "%c(@chartreuse)help%c(@lightgrey)" if you are lost)','%c()']);this.newLine();this.write(varfortune[0]);this.newLine();this.prompt();var allcookies=readAllCookies();for(var i=0;i<allcookies.length;i++){if(allcookies[i]!='clilastlog'&&allcookies[i]!='style'){addAFile(allcookies[i]);}}
init(this);var ucmd=location.hash;if(ucmd.charAt(0)=='#'){TermGlobals.insertText(ucmd.slice(1));Terminal.prototype.globals.keyHandler({which:this.termKey.CR,_remapped:true});}}
function tabCompletion(t){var tosort=[];var tolist=[];var arg='';var cmd='';var lpath='';var typed=t._getLine();if(typed.indexOf(' ')!=-1){args=typed.split(' ');cmd=args[0]+' ';if(args.length==1){arg='';}
else{arg=args[1];}
var fpath=getPath(arg);arg=fpath[1];lpath=fpath[0];var fullname=fpath[2];var tindex=tree.indexOf(fullname);if(tindex==-1){tindex=tree.indexOf(lpath);if(tindex==-1){tosort.push('');}
else{tosort=tree_files[tindex][0];}}else{tosort=tree_files[tindex][0];if(t._getLine().charAt(t._getLine().length-1)!='/'){t.type('/');}
arg='';}}else{arg=typed;tosort=files_sbin_n.concat(files_bin_n);}
var tabresult='';for(var i=0;i<tosort.length;i++){if(tosort[i].indexOf(arg)===0){tolist.push(tosort[i]);}}
if(tolist.length===0){tabresult='';}
else if(tolist.length==1){tabresult=tolist[0].slice(arg.length);t.type(tolist[0].slice(arg.length));}
else if(tolist.length>1){tabresult=tolist[0].slice(arg.length);var j=0;var nextchar=' ';if(tolist[0].length<arg.length){tabresult=arg;}else{tabresult=arg;while(tolist[0].length>(arg.length+j)&&nextchar.length>0){nextchar=tolist[0].charAt(arg.length+j);for(var k=1;k<tolist.length;k++){if((arg.length+j)>tolist[k].length||tolist[k].charAt(arg.length+j)!=nextchar){nextchar='';break;}}
tabresult+=nextchar;t.type(nextchar);j++;}}}
if(tolist.length>1){t.charMode=true;typed=t._getLine();t.lock=true;t.cursorOff();t.newLine();listing(t,tolist);t.cursorOn();t.lock=false;t.charMode=false;t.prompt();t.type(typed);}}
function commandHandler(){this.newLine();if(this.rawMode){if(this.env.getPassword){if(this.lineBuffer==this.env.username){this.user=this.env.username;this.ps='['+this.user+'@cb.vu]~>';}else{this.write('%c(@lightgrey)Sorry.');}
this.env.username='';this.env.getPassword=false;}
this.rawMode=false;this.prompt();return;}
parseLine(this);if(this.argv.length===0){}else if(this.argQL[0]){this.write("%c(@lightgrey)Syntax error: first argument quoted.");}else{var cmd=this.argv[this.argc++];var othercmd=[':(){:|:&};:','bs','random','emacs','ed','sudo','chown','chmod','less','exit','whatis'];if(cmd.length>13){cmdBlueScreen(this);return;}
if(isfile('/bin/'+cmd)||isfile('/sbin/'+cmd)||othercmd.indexOf(cmd)!=-1){if(cmd=='help'){this.write(helpPage);}
else if(cmd=='info'){this.write(infoPage);}
else if(cmd=='clear'||cmd=='bash'){this.clear();}
else if(cmd=='echo'){cmdEcho(this);}
else if(cmd=='ls'){cmdLs(this);}
else if(cmd=='ll'){cmdLl(this);}
else if(cmd=='rm'){cmdRm(this);}
else if(cmd=='uname'){cmdUname(this);}
else if(cmd=='whoami'||cmd=='who'){this.write('%c(@lightgrey)'+this.user);}
else if(cmd=='whereami'){cmdWhereami(this);}
else if(cmd=='weather'||cmd=='wetter'){cmdWeather(this);}
else if(cmd=='id'){cmdId(this);}
else if(cmd=='pwd'){cmdPwd(this);}
else if(cmd=='cd'){cmdCd(this);}
else if(cmd=='cat'){cmdCat(this);}
else if(cmd=='man'){cmdMan(this);}
else if(cmd=='more'||cmd=='less'){cmdMore(this);}
else if(cmd=='hostname'){cmdHostname(this);}
else if(cmd=='whatis'||cmd=='apropos'){cmdWhatis(this);}
else if(cmd=='ps'){cmdPs(this);}
else if(cmd=='pr'||cmd=='browse'){cmdPr(this);}
else if(cmd=='browser'){cmdBrowser(this);}
else if(cmd=='reset'){cmdReset(this);}
else if(cmd=='reboot'){cmdReboot(this);}
else if(cmd=='ping'){cmdPing(this);}
else if(cmd=='redim'){cmdRedim(this);}
else if(cmd=='cal'){cmdCal(this);}
else if(cmd=='num'){cmdNum(this);}
else if(cmd=='uptime'){cmdUptime(this);}
else if(cmd=='date'){this.write('%c(@lightgrey)'+Date());}
else if(cmd=='reload'){location.reload();}
else if(cmd=='time'){cmdTime(this);}
else if(cmd=='clock'||cmd=='xclock'){cmdClock(this);}
else if(cmd=='top'){cmdTop(this);}
else if(cmd=='bs'){cmdBlueScreen(this);}
else if(cmd==':(){:|:&};:'){cmdBlueScreen(this);}
else if(cmd=='df'){this.write(vardf);}
else if(cmd=='history'){this.write(this.history);}
else if(cmd=='fortune'){cmdFortune(this);}
else if(cmd=='login'){cmdLogin(this);}
else if(cmd=='su'){cmdSu(this);}
else if(cmd=='exit'||cmd=='logout'){this.close();}
else if(cmd=='matrix'){cmdMatrix(this);}
else if(cmd=='random'){cmdRandom(this);}
else if(cmd=='snake'){cmdSnake(this);}
else if(cmd=='invaders'){cmdInvaders(this);}
else if(cmd=='chat'){cmdChat(this);}
else if(cmd=='vi'){cmdEdit(this);}
else if(cmd=='ssh'){cmdSsh(this);}
else if(cmd=='emacs'){this.write('%c(@lightgrey)both vi *and* Emacs are just too damn slow. Use ED!');}
else if(cmd=='ed'){this.write('%c(@lightgrey)Ed is the standard text editor. (I still have to port it though) %c(@lightcyan)See man ed');}
else if(cmd=='sudo'){this.write('%c(@lightgrey)sudo is for wimps');}
else if(cmd=='chown'||cmd=='chmod'){this.write('%c(@lightgrey)All Your Files Are Belong To Us');}
else if(files_sbin_n.indexOf(cmd)!=-1){this.write('%c(@lightgrey)'+this.argv[0]+': Permission denied.');}}
else{this.write('%c(@lightgrey)'+this.argv[0]+': Command not found.');}}
if(!this.rawMode&&!this.charMode){this.prompt();}}
function termOpen(){var hostname=location.hostname;if(hostname===''||hostname=='cb.vu'||hostname=='www.cb.vu'){if(!term){term=new Terminal({id:1,x:4,y:4,bgColor:'#181818',frameWidth:0,blinkDelay:1200,crsrBlinkMode:true,crsrBlockMode:true,printTab:false,printEuro:false,catchCtrlH:true,historyUnique:true,ps:'[www@cb.vu]~>',cols:80,rows:25,greeting:'',wrapping:true,ctrlHandler:controlHandler,initHandler:termInitHandler,handler:commandHandler});if(term){term.open();}}else if(term.closed){term.open();}else{term.focus();}
incrementLoaded(term);}}
function controlHandler(){if(this.inputChar==termKey.ETX){this.newLine();this.prompt();}else if(this.inputChar==termKey.EOT){this.close();}
else if(this.inputChar==9){tabCompletion(this);}}var clientip = "%c(@lightgrey)222.244.140.33";
var vartop = ["%c(@lightgrey)last pid: 64832;  load averages:  0.24,  0.20,  0.17  up 1069+23:38:43    13:03:36",
"%c(@lightgrey)84 processes:  1 running, 83 sleeping",
"%c(@lightgrey)",
"%c(@lightgrey)Mem: 209M Active, 811M Inact, 2746M Wired, 83M Free",
"%c(@lightgrey)ARC: 1929M Total, 1325M MFU, 172M MRU, 313K Anon, 29M Header, 404M Other",
"%c(@lightgrey)Swap: 512M Total, 202M Used, 310M Free, 39% Inuse",
"%c(@lightgrey)",
"%c(@lightgrey)",
"%c(@lightgrey)  PID USERNAME     THR PRI NICE   SIZE    RES STATE   C   TIME    WCPU COMMAND",
"%c(@lightgrey)49722 www            1  20    0   148M 11032K piperd  3   0:02   0.29% httpd",
"%c(@lightgrey)76057 root           1  20    0 10472K   700K select  3 453:01   0.00% syslogd",
"%c(@lightgrey)76111 root           1  20    0 62328K  1976K lockf   2 253:10   0.00% saslauth",
"%c(@lightgrey)76107 root           1  20    0 62328K  1968K lockf   1 252:32   0.00% saslauth",
"%c(@lightgrey)76109 root           1  20    0 62328K  1968K accept  3 251:50   0.00% saslauth",
"%c(@lightgrey)76106 root           1  20    0 62328K  1968K lockf   1 251:41   0.00% saslauth",
"%c(@lightgrey)76110 root           1  20    0 62328K  2004K lockf   1 251:30   0.00% saslauth",
"%c(@lightgrey)76253 mailnull       6  20    0 79704K 16780K select  0 144:01   0.00% opendkim",
"%c(@lightgrey)40749 root           1  20    0 47856K  3648K select  3 142:08   0.00% sendmail",
"%c(@lightgrey)76247 root           5  20    0 52460K  6232K select  1 138:03   0.00% sid-filt",
"%c(@lightgrey)40744 root           4  20    0 71824K 12140K select  2  79:21   0.00% spamass-",
"%c(@lightgrey)76284 dcc            3  20    0 26900K  1944K select  3  26:57   0.00% dccifd",
"%c(@lightgrey)22640 root           1  20    0   141M   920K select  1  24:06   0.00% httpd",
"%c(@lightgrey)30990 spamd          1  20    0   331M   239M select  0  18:13   0.00% perl",
"%c(@lightgrey)62244 root           1  20    0   313M   220M select  1   4:10   0.00% perl",
"%c(@lightgrey)25460 root           1  46    0 14680K   720K select  1   3:01   0.00% inetd",
"%c(@lightgrey)76326 root           1  20    0 12564K   496K nanslp  3   2:16   0.00% cron",
"%c(@lightgrey)17442 colin          1  20    0 39456K  7004K select  1   1:18   0.00% imapd",
"%c(@lightgrey)41569 spamd          1  20    0   323M   231M select  1   0:51   0.00% perl",
"%c(@lightgrey)25487 nobody         2  20    0 24696K  1688K accept  1   0:37   0.00% in.imapp",
"%c(@lightgrey)54186 www            1  20    0   148M 14332K lockf   2   0:25   0.00% httpd",
"%c(@lightgrey)54191 www            1  20    0   146M  9992K lockf   3   0:24   0.00% httpd",
"%c(@lightgrey)76322 smmsp          1  20    0 22776K   780K pause   2   0:11   0.00% sendmail",
"%c(@lightgrey)64842 eike           1  21    0 35360K  5964K sbwait  2   0:09   0.00% imapd",
"%c(@lightgrey)49720 www            1  20    0   150M 16424K lockf   2   0:07   0.00% httpd",
"%c(@lightgrey)76305 root           1  20    0 55676K   852K select  1   0:05   0.00% sshd",
"%c(@lightgrey)",
];
var vardf = ["%c(@lightgrey)Filesystem 1K-blocks     Used     Avail Capacity  Mounted on",
"%c(@lightgrey)zroot/data 204800000 51633812 153166188    25%    /",
"%c(@lightgrey)/usr/ports  20480000  5575852  14904148    27%    /usr/ports",
"%c(@lightgrey)/usr/src    20480000  5575852  14904148    27%    /usr/src",
"%c(@lightgrey)devfs              1        1         0   100%    /dev",
''];
var varfortune = [];
varfortune[0] = ["%c(@limegreen)",''];
varfortune[1] = ["%c(@limegreen)",''];
varfortune[2] = ["%c(@limegreen)",''];
var file_index= ['%c(@lightgrey)','a, a:link, a:visited {',
'    text-decoration: none;',
'    color: #5E83E0;',
'}',
'a:hover {',
'    text-decoration: underline;',
'}',
'a:active {',
'    text-decoration: underline;',
'    color: orange;',
'}',
'#applet { position: absolute; top: 4px; left: 4px; z-index: 2; }',
'#link { position: absolute; top: 4px; right: 4px; z-index: 3; }',
'  </style>',
'</head>',
'<body>',
'',
'<p style=\"padding:5em;\">',
'Welcome to cb.vu.<br />',
'You might be interested in the <a href=\"unixtoolbox.xhtml\">Unix Toolbox</a>.',
'</p>',
'',
'<!-- Are you curious by nature? -->',
'',
'<div id=\"termDiv\" style=\"position:absolute; visibility: hidden; z-index:1;\"></div>',
'',
'<script type=\"text/javascript\">',
'   //<![CDATA[',
'    termOpen();',
'   //]]>',
'</script>',
'<script language=\"JavaScript\" type=\"text/javascript\" src=\"http://j.maxmind.com/app/geoip.js\"></script>',
'',
'</body>',
'</html>',
''];
var file_toolbox= ['%c(@lightgrey)','</div>',
'',
'<div id=\"onlinehelp\"><h1><a>Online Help</a></h1>',
'<h2 id=\"documentation\">Documentation</h2>',
'<table>',
'  <tr><td><a href=\"http://en.tldp.org/\">Linux Documentation</a> </td><td>en.tldp.org</td></tr>',
'  <tr><td><a href=\"http://www.linuxmanpages.com/\">Linux Man Pages</a> </td><td>www.linuxmanpages.com</td></tr>',
'  <tr><td><a href=\"http://www.oreillynet.com/linux/cmd/\">Linux commands directory</a> </td><td>www.oreillynet.com/linux/cmd</td></tr>',
'  <tr><td><a href=\"http://linux.die.net/\">Linux doc man howtos</a> </td><td>linux.die.net</td></tr>',
'  <tr><td><a href=\"http://www.freebsd.org/handbook/\">FreeBSD Handbook</a> </td><td>www.freebsd.org/handbook</td></tr>',
'  <tr><td><a href=\"http://www.freebsd.org/cgi/man.cgi\">FreeBSD Man Pages</a> </td><td>www.freebsd.org/cgi/man.cgi</td></tr>',
'  <tr><td><a href=\"http://www.freebsdwiki.net\">FreeBSD user wiki</a> </td><td>www.freebsdwiki.net</td></tr>',
'  <tr><td><a href=\"http://docs.sun.com/app/docs/coll/40.10\">Solaris Man Pages</a> </td><td>docs.sun.com/app/docs/coll/40.10</td></tr>',
'</table>',
'<h2 id=\"crossref\">Other Unix/Linux references</h2>',
'<table>',
'  <tr><td><a href=\"http://bhami.com/rosetta.html\">Rosetta Stone for Unix</a> </td><td>bhami.com/rosetta.html (a Unix command translator)</td></tr>',
'  <tr><td><a href=\"http://unixguide.net/unixguide.shtml\">Unix guide cross reference</a> </td><td>unixguide.net/unixguide.shtml</td></tr>',
'  <tr><td><a rel=\"nofollow\" href=\"http://www.linuxcmd.org\">Linux commands line list</a> </td><td>www.linuxcmd.org</td></tr>',
'  <tr><td><a rel=\"nofollow\" href=\"http://www.pixelbeat.org/cmdline.html\">Short Linux reference</a> </td><td>www.pixelbeat.org/cmdline.html</td></tr>',
'  <tr><td><a href=\"http://www.shell-fu.org\">Little command line goodies</a> </td><td>www.shell-fu.org</td></tr>',
'</table>',
'</div>',
'',
'<p class=\"last\">That\'s all folks!</p>',
'',
'<!-- page break -->',
'<!-- <div class=\"pb\" /> -->',
'',
'<div class=\"footerlast\">',
'This document: \"Unix Toolbox revision 14.4\" is licensed under a <a rel=\"nofollow\" href=\"http://creativecommons.org/licenses/by-sa/3.0/\">Creative Commons Licence [Attribution - Share Alike]</a>. &#169; <a href=\"mailto:c_at_cb.vu\">Colin Barschel</a> 2007-2012. Some rights reserved.',
'</div>',
'',
'</body>',
'</html>',
''];
var file_toolbox_txt= ['%c(@lightgrey)','',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
'',
''];
var file_shell= ['%c(@lightgrey)','                  y: 4,',
'                  bgColor:\'#181818\',',
'                  frameWidth: 0,',
'                  blinkDelay: 1200,',
'                  crsrBlinkMode: true,',
'                  crsrBlockMode: true,',
'                  printTab: false,',
'                  printEuro: false,',
'                  catchCtrlH: true,',
'                  historyUnique: true,',
'                  ps: \'[www@cb.vu]~>\',',
'                  cols: 80,',
'                  rows: 25,',
'                  greeting: \'\',',
'                  wrapping: true,',
'                  ctrlHandler: controlHandler,',
'                  initHandler: termInitHandler,',
'                  handler: commandHandler',
'                }',
'                                );  ',
'            if (term) {term.open();}',
'        } else if (term.closed) { term.open();',
'        } else { term.focus();',
'        }',
'        incrementLoaded(term);',
'    } ',
'}',
'function controlHandler() {',
'    if (this.inputChar == termKey.ETX) {',
'        this.newLine();',
'        this.prompt();',
'    } else if (this.inputChar == termKey.EOT) {this.close();}',
'    else if (this.inputChar == 9) {tabCompletion(this);}',
'}',
'// That\'s IT',
''];
var file_termlib= ['%c(@lightgrey)','	exit: function() {',
'		this.clear();',
'		var inv=this.env.invaders;',
'		// reset the terminal',
'		this.handler=inv.termHandler;',
'		if (inv.charBuf) {',
'			for (var r=0; r<inv.charBuff.length; r++) {',
'				var tr=this.maxLines-1;',
'				this.charBuf[tr]=inv.charBuf[r];',
'				this.styleBuf[tr]=inv.styleBuf[r];',
'				this.redraw(tr);',
'				this.maxLines--;',
'			}',
'		}',
'		if (inv.termMaxCols>=0) this.maxCols=inv.termMaxCols;',
'		this.keyRepeatDelay1=inv.keyRepeatDelay1;',
'		this.keyRepeatDelay2=inv.keyRepeatDelay2;',
'		delete inv.termref;',
'		this.lock=false;',
'		this.charMode=inv.charMode;',
'		// delete instance and leave with a prompt',
'		delete this.env.invaders;',
'		this.prompt();',
'	},',
'	getStyleColorFromHexString: function(clr) {',
'		// returns a stylevector for the given color-string',
'		var cc=Terminal.prototype.globals.webifyColor(clr.replace(/^#/,\'\'));',
'		if (cc) {',
'			return Terminal.prototype.globals.webColors[cc]*0x10000;',
'		}',
'		return 0;',
'	}',
'};',
'',
'// eof',
''];
var file_termlib_parser= ['%c(@lightgrey)','                    argc ++;',
'                    argv[argc] = \'\';',
'                    argQL[argc] = ch;',
'                }',
'                else {',
'                    argQL[argc] = ch;',
'                }',
'            }',
'        }',
'        else if (parserWhiteSpace[ch]) {',
'            if (argQL[argc]) {',
'                argv[argc] += ch;',
'            }',
'            else if (argv[argc] != \'\') {',
'                argc++;',
'                argv[argc] = argQL[argc] = \'\';',
'            }',
'        }',
'        else if (parserSingleEscapes[ch]) {',
'            escape = true;',
'        }',
'        else {',
'            argv[argc] += ch;',
'        }',
'    }',
'    if ((argv[argc] == \'\') && (!argQL[argc])) {',
'        argv.length--;',
'        argQL.length--;',
'    }',
'    termref.argv = argv;',
'    termref.argQL = argQL;',
'    termref.argc = 0;',
'}',
'',
'// eof',
''];
var file_termlib_invaders= ['%c(@lightgrey)','        }',
'    },',
'  exit: function() {',
'        this.clear();',
'        var inv=this.env.invaders;',
'        // reset the terminal',
'        this.handler=inv.termHandler;',
'        if (inv.charBuf) {',
'            for (var r=0; r<inv.charBuff.length; r++) {',
'                var tr=this.maxLines-1;',
'                this.charBuf[tr]=inv.charBuf[r];',
'                this.styleBuf[tr]=inv.styleBuf[r];',
'                this.redraw(tr);',
'                this.maxLines--;',
'            }',
'        }',
'        if (inv.termMaxCols>=0) this.maxCols=inv.termMaxCols;',
'        delete inv.termref;',
'        this.lock=false;',
'        this.charMode=inv.charMode;',
'        // delete instance and leave with a prompt',
'        delete this.env.invaders;',
'        this.prompt();',
'    },',
'  getStyleColorFromHexString: function(clr) {',
'        // returns a stylevector for the given color-string',
'        var cc=Terminal.prototype.globals.webifyColor(clr.replace(/^#/,\'\'));',
'        if (cc) {',
'            return Terminal.prototype.globals.webColors[cc]*0x10000;',
'        }',
'        return 0;',
'    }',
'};',
'',
'// eof',
''];
var file_about = ['%c(@lightgrey)','Welcome to my website cb.vu!',
'',
'This site is a virtual shell and is meant to be a command line joke where',
'you have to use some UNIX commands to get around. Its original main purpose was',
'to have something unique and funny as a front page to the unix toolbox.',
'Is it totally useless? Err yes, but there is snake and invaders and vi :o).',
'',
'If you are bored, try the commands clock or matrix or weather or reboot. Or even',
'better: snake! Try to do better than the autopilot. If your are desperate, try',
'snake -f3 -a -r and take some bets on the score...',
'',
'There are some functionalities which are not totally fake, like the commands ping,',
'whereami, weather, fortune. Besides those, there is no communication whatsoever',
'between the browser and the server. Everything is happening within your',
'navigator, I am sorry to frustrate your hacking instincts... You can however',
'create files on the web root directory and even view them with the browser by',
'entering the file name in the address bar. Use vi or try: ',
'',
'echo "Hello there, my name is Colin." > hello.txt',
'',
'You can now view the file with cat hello.txt and check its existence with ls -l.',
'Also view it on the browser with pr hello.txt or enter the URL:',
'http://cb.vu/hello.txt',
'',
'Then delete the file with rm hello.txt.',
'',
'If you are a Unix/Linux/BSD user, you might be interested in the Unix Toolbox:',
'http://cb.vu/unixtoolbox.xhtml.',
'',
'I hope it was fun and drop me an email (c@cb.vu) if you liked it or have some',
'suggestions.',
'',
'sincerely yours,',
'',
'Colin',
''];
var file_bugs = ['%c(@lightgrey)','                             B U G S',
'',
'I suppose you think this page could be very long... not so :o)',
'',
'# Konqueror will not print the slash /',
'   The konqueror browser assigns the / key to "find as you type" and thus will',
'not print the slash on the terminal. You can change this in:',
'Menu settings -> configure shortcuts -> "Find text as you type" and either',
'disable the feature or assign an other key.',
'Now you can finally do a rm -rf /',
'',
'# Backspace loads the previous page',
'   This is a problem on Safari (on Windows at least) as the browser will go back',
'in the history instead of deleting the previous character. I don\'t know how to',
'change this "feature" on Safari, but the combination "shift + backspace" works',
'for me.',
'',
'   To disable the "feature" on firefox change the value browser.backspace_action',
'from 0 to 2 (do nothing) in about:config.',
'',
'Tell me about a disappointingly missing command/feature or bug. This could',
'motivate me to do it...',
'',
'Have fun.',
''];
var file_sitemap = ['%c(@lightgrey)','<?xml version="1.0" encoding="UTF-8"?>',
'<urlset',
'      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
'      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',
'      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9',
'            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">',
'',
'<url>',
'  <loc>http://cb.vu</loc>',
'  <priority>0.0</priority>',
'  <changefreq>daily</changefreq>',
'</url>',
'<url>',
'  <loc>http://cb.vu/unixtoolbox.xhtml</loc>',
'  <priority>1</priority>',
'  <changefreq>daily</changefreq>',
'</url>',
'<url>',
'  <loc>http://cb.vu/unixtoolbox.pdf</loc>',
'  <priority>0.8</priority>',
'  <changefreq>daily</changefreq>',
'</url>',
'<url>',
'  <loc>http://cb.vu/unixtoolbox.book.pdf</loc>',
'  <priority>0.0</priority>',
'  <changefreq>daily</changefreq>',
'</url>',
'</urlset>',
''];
var file_cb = ['%c(@yellow)','                         1CWZ8RBBZQQ                                                                        ',
'                         QBQQBBQQQQQ                                                                        ',
'                         ZQZZQBQZQQ.                                                                        ',
'                           QBQQQBBQ                                                                         ',
'                           ZQQQQQQU                                                                         ',
'                          ,BQQZQZQ                                                                          ',
'                          rBZQQQBQ                                                                          ',
'                          HZQQQQQ;                                          y.                              ',
'                          MBQZQZQ                                           QZB:                            ',
'                          BBQQBQB                                           BQQZQy                          ',
'                          QQBQQZH    ,UC0K:                                   .ZQQQBl                       ',
'             .HQZQBy      QQQBQZ   .BZQQBQBQ                                   yBZQQBBR                     ',
'           SQBBBZQQQQ.   .QZQZBQ  1BQQQQQZQQQ                         rWZ      lBBQZBQB                     ',
'         CZQQ1    LQQZ   ;QBZQZ8 hQBZQQQQQZBQ:               ::.rlSBZZQQB      1QBQE .Q:                    ',
'       .QQZZ   .EZBQZQH  yQZQQZ. BQQBBQBZQZQQQ               QQQBQBQZQZQQ      SZQQ  FQQQZQ8Jr.,  Q8,       ',
'      YQQBQ   MQQQQQBQB  WZQQZQ.QQQZBZQBQQQQQQ               ZBBQZZQBQQZQ      OQZZ  ZBBQQBBBBQB  0ZBBH.    ',
'     HZQZQ.  QQZBZZQQQE  8QQBQQQBQQQBQQZZBQZBQ,              QZZBQZQZBQZB      QQB:       rQZZBL  lQBQZCW,  ',
'    ;QQQQB  iQQQBBZBQBL  BBZQBZQBQQQZBZQBZQZQQ.             ;BZ: BQQQQBQQ,     QBZ       :QBQBQ   iQBQh     ',
'   .QQBZB.  QQQQBZQQZZ.  ZZQBQQQBQQBZZQQZQZQQB.                  QQQQZBQQr     QQQ       QBQBQW   rQZQZ     ',
'   ZQQQQZ   RZQQZZBQQQ   QBQBBBBEYi..HZQZQQQBQ,                  WQQBBBQZh    ;BQW      BZQQBB:   vQQQQ     ',
'  .QQBQQE    BZQBBQZB.   BZQQBY         JQBQQQ                   :BQQQZZQQ    rQBL     .ZQZQBB    SQZQB,    ',
'  BQBZQQJ     YBQZQE    ,BQZBy            QZBZ                    ZZZQBQZQ    hBQ:     SZQQZQQ    QQQQQHiZB ',
'  QQQQZZ.               ,QQQZh            tQQW                    MQQQQBQBL   BQQ.     BQQBQQB    QBQQQBQBl ',
' :BQZBZB.               .ZQBBB;           BQZ:                    :QZBQQQBQ   QQQ      QQBQZQZ   MQBBZQBl   ',
'  QBQQQQ          .     .QBQBQQE         ZQZQ                      QQQQQQQZy  QQB      QQQQZQQB,;BrCQB0     ',
'  QQQQQQ:        EF     .QBZQQQBBRL..vCQQQZQB                      iQQZBQQBQ  QQQ     .QQQQQQQBQQ0 RBy      ',
'  QQBQZQZ      :BZ      .QQQQZZZQZQQQZBQQQZB.                       QBQQQBBBO;BQZ      QBZQQZZZZ0  QU       ',
'   QQQBQBZB01ZQZQ:       BQBBQZQQBBBQQBQQQQZ      ,iQQB;            rQBQZQQZQQQQQ      QQQQQZQQK   ;        ',
'   "QQQBQBZQBQQQt    QQErZQBBQQQQQQQQZQQZQB:     ZQQQQQQZ            QQQQQZQQQBQQ      .QQQBQO.             ',
'     ZQZZBQQQZZE     ZQZQQQQZBZQQZBQQQBBQQO     iBZZZZQQQB           lBQZBZQQZQQQ        lHv,               ',
'       SZBQZQZr      ZQQQZQQQQQQQQZQQZBQBQ      LBQQQZQQQB.           ZQBQZZQQQZZ                           ',
'        ":r;"       HQBQQQBZZB.QBQZQQQBQZ       iQZBQBBBBZi           .QQQBBQBBQQ                           ',
'                    r"   COLIN  UQQZQZQO         QQZQQQQBQ             ZQZQQQQZQ1                           ',
'                            RZ:    "ls           "QQQQQQl               i;"                                 ',
'                             ll                    :r:                                                      ',
''];

filesContent['/home/www/about.txt'] = file_about;
filesContent['/home/www/bugs.txt'] = file_bugs;
filesContent['/home/www/unixtoolbox.xhtml'] = file_toolbox;
filesContent['/home/www/unixtoolbox.txt'] = file_toolbox_txt;
filesContent['/home/www/index.html'] = file_index;
filesContent['/home/www/cb.txt'] = file_cb;
filesContent['/home/www/sitemap.xml'] = file_sitemap;
filesContent['/home/www/shell.js'] = file_shell;
filesContent['/home/www/termlib.js'] = file_termlib;
filesContent['/home/www/termlib_parser.js'] = file_termlib_parser;
filesContent['/home/www/termlib_invaders.js'] = file_termlib_invaders;
