/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2017 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/
var vbphrase=(typeof (vbphrase)=="undefined"?new Array():vbphrase);(function(A,B){A("[data-role=page]").live("pagecreate",function(D){var C=this;if(A(".cmssections ul li",C).length==0){A("a.cmssectionlink",C).remove()}if(A(".categories ul li",C).length==0){A("a.cmscategorylink",C).remove()}});A("[data-role=page]").live("pageinit",function(C){var N=this;A("div#header",N).after(A(".cms_primarycontent",N));A("div#.pagetitle",N).after(A(".cms_toolbar",N));A("a.scrolltop").click(function(){A.mobile.silentScroll();return false});A("a.fullsitelink",N).click(function(){var Q=window.location.href;var R=Q.lastIndexOf("#");var T="";if(R>0){T=Q.substring(R);Q=Q.substring(R,0)}var P=-1;if(USER_DEFAULT_STYLE_TYPE=="standard"){P=USER_STYLEID}if(Q.indexOf("styleid=")!=-1){var S=/[\?&]*styleid=-?\d+&?/g;Q=Q.replace(S,"")}if(Q.search(/\?/)==-1){window.top.location=Q+"?styleid="+P+T}else{window.top.location=Q+"&styleid="+P+T}return false});A("input[name='newforumpwd']",N).parent().siblings().eq(0).hide();A("[data-role='vbpagenav']",N).vbpagenav();A("[data-role='imagereg']",N).imagereg();A('[name="preview"]',N).unbind().live("vclick",function(P){A("<input>").attr({type:"hidden",id:"previewinput",name:"preview",value:"1"}).appendTo("form");A(this).closest("form").submit();return false});A("a.newreplylink",N).unbind().click(function(Q){var P=A("#qr_threadid",N).val();A.ajax({type:"POST",url:"ajax.php?do=getquotes&t="+P,data:SESSIONURL+"securitytoken="+SECURITYTOKEN+"&do=getquotes&t="+P,success:function(S){var R=A(S).find("quotes").text();G(R)},dataType:"xml"});return false});A('.quickreplycontainer [name="cancel"]').unbind().live("click",function(Q){var P=A(this).closest(".quickreplycontainer");P.remove();return false});A("a.quickreply").unbind().click(function(P){var Q=A(this).attr("id").substr(3);if(!A("#qrcontainer_"+Q,N).length){K(this,"");A("#qrcontainer_q_"+Q,N).remove()}return false});A("a.quickreplyquote").unbind().click(function(P){var Q=A(this).attr("id").substr(5);if(!A("#qrcontainer_q_"+Q,N).length){A.ajax({type:"POST",url:"ajax.php?do=getquotes&p="+Q,data:SESSIONURL+"securitytoken="+SECURITYTOKEN+"&do=getquotes&p="+Q,success:O,dataType:"xml",context:A(this)})}return false});function O(Q){var P=A(Q).find("quotes").text();var R=A(this).attr("id").substr(5);if(P){K(this,P);A("#qrcontainer_"+R,N).remove()}}function K(S,R){var T=A(S).attr("id").substr(3);if(!A("#qrcontainer_"+T,N).length){var Q=A(".qr_defaultcontainer",N).clone().attr("id","qrcontainer_"+T).removeClass("qr_defaultcontainer");Q.find('input[name="p"]').val(T);var P=Q.find('textarea[name="message"]');if(R.length){P.val(R)}P.textinput();Q.insertAfter(A(S).closest("div.postfoot")).show();Q.parent().siblings().find(".quickreplycontainer").remove()}}function G(R){var Q;if(!A("#qrcontainer_default",N).length){Q=A(".qr_defaultcontainer",N).clone().attr("id","qrcontainer_default").removeClass("qr_defaultcontainer")}else{Q=A("#qrcontainer_default",N)}var P=Q.find('textarea[name="message"]').textinput();if(R.length){P.val(R)}Q.insertAfter(A(".qr_defaultcontainer",N)).show();A.mobile.silentScroll(Q.position().top)}A("a.tablink",N).each(function(){A(this).click(A(this),E);A(this).closest("li").unbind("click").click(A(this),E);A(this).closest("li > .ui-btn-inner").unbind("click").click(A(this),E)});function E(R){var P=R.data;var S=P.attr("id").substr(4);var Q=P.parents("[data-role=content]").find(".page-"+S);if(!Q.length){A("[data-role=page]").animationComplete(function(){A("a.tablink").removeClass("ui-btn-active");A("a.tab-"+S).not(".tablinklist").addClass("ui-btn-active");var T=A(".page-"+S);T.removeClass("hidden").show().siblings(".tabbody").addClass("hidden");T.find(".ui-listview").listview("refreshthumbs")});return true}if(P.hasClass("tablinklist")){A("a.tablink").not(".tablinklist").removeClass("ui-btn-active")}else{P.addClass("ui-btn-active").parent().siblings("li").find("a").removeClass("ui-btn-active")}Q.removeClass("hidden").show().siblings(".tabbody").addClass("hidden");Q.find(".ui-listview").listview("refreshthumbs");return false}A(".navbar").each(function(){var P=A(this).parent().find("div.pagetitle");if(P.length){P.after(A(this))}});A("a.dismiss").click(function(){var P=A(this).attr("id").substr(14);A.ajax({type:"POST",url:"ajax.php?do=dismissnotice",data:SESSIONURL+"securitytoken="+SECURITYTOKEN+"&do=dismissnotice&noticeid="+P,success:L,dataType:"xml",context:A(this)})});function L(P){var Q=A(P).find("dismissed").text();A("li.navbar_notice_"+Q).remove()}if(A(".sidebar_container .categories ul li",N).length){A("a.blogcategorylink",N).show().click(function(){if(!A(this).siblings(".categories").length){A(this).after(A(".sidebar_container .categories",N).first().clone(true));A(this).siblings(".content_container",N).hide()}else{A(this).siblings(".categories",N).remove();A(this).siblings(".content_container",N).show()}return false});A("button.categoriesback",N).click(function(){var P=A(this).parents("div.categories");P.siblings(".content_container",N).show();P.remove()})}if(A(".sidebar_container .blogusername",N).length){A(".memberinfoheader .userinfo",N).html(A(".sidebar_container .blogusername",N).html());A(".memberinfoheader .avatar",N).html(A(".sidebar_container .bloguseravatar",N).html())}function H(Q){var P=navigator.userAgent;if(A.browser.opera&&P.search("Opera Mini")){window.location.hash=Q}return false}A("a.blogpostcommentlink",N).click(function(){H("#qc_form");A.mobile.silentScroll(A("form#qc_form",N).last().position().top);return false});A("a.blogcommentslink",N).click(function(){if(A("#posts").length>0){H("#posts")}else{if(A("#qc_form").length>0){H("#qc_form")}}A.mobile.silentScroll(A("div.comments",N).last().position().top);return false});A("a.articlecommentlink",N).click(function(){if(A("#posts").length>0){H("#posts")}else{if(A("#form_widget_comments").length>0){H("#form_widget_comments")}}A.mobile.silentScroll(A("#form_widget_comments",N).last().position().top);return false});A('[name="draft"]',N).unbind().click(function(P){A("<input>").attr({type:"hidden",id:"draftinput",name:"draft",value:"1"}).appendTo("form");A(this).closest("form").submit();return false});if(A("ul.breadcrumb li",N).length>1){A(N).data("breadcrumbshow",false);A("div.pagetitle h1",N).before(A("<a>").attr("href","#").html(A("<img>").attr("src",IMGDIR_MOBILE+"/arrow-left.png").addClass("backbutton")).click(function(P){P.preventDefault()}));A("div.pagetitle",N).click(function(){if(!A(N).data("breadcrumbshow")){A(".breadcrumb",N).show();var P=A(".breadcrumb",N).offset();A(".breadcrumboverlay",N).offset(P).show();A("div.pagetitle img.backbutton",N).attr("src",IMGDIR_MOBILE+"/arrow-down.png");A(N).data("breadcrumbshow",true)}else{A(".breadcrumb",N).hide();A(".breadcrumboverlay",N).offset({top:0,left:0}).hide();A("div.pagetitle img.backbutton",N).attr("src",IMGDIR_MOBILE+"/arrow-left.png");A(N).data("breadcrumbshow",false)}}).after(A("<div>").addClass("breadcrumboverlay"))}if(A(".cmssections ul li",N).length){A("a.cmssectionlink",N).show().click(function(){if(!A(this).parent().siblings(".cmssections").length){A(this).parent().siblings(".content_container",N).before(A(".cmssections",N).first().clone(true));A(this).parent().siblings(".categories",N).remove();A(this).parent().siblings(".content_container",N).hide()}else{A(this).parent().siblings(".cmssections",N).remove();A(this).parent().siblings(".content_container",N).show()}return false});A("button.cmssectionsback",N).click(function(){var P=A(this).parents("div.cmssections");P.siblings(".content_container",N).show();P.remove()})}if(A(".categories ul li",N).length){A("a.cmscategorylink",N).show().click(function(){if(!A(this).parent().siblings(".categories").length){A(this).parent().siblings(".content_container",N).before(A(".categories",N).first().clone(true));A(this).parent().siblings(".cmssections",N).remove();A(this).parent().siblings(".content_container",N).hide()}else{A(this).parent().siblings(".categories",N).remove();A(this).parent().siblings(".content_container",N).show()}return false});A("button.cmscategoriesback",N).click(function(){var P=A(this).parents("div.categories");P.siblings(".content_container",N).show();P.remove()})}A("button[data-submitname]",N).each(function(){A(this).unbind().click(function(R){var P=A(this).attr("data-submitvalue");var Q=A(this).attr("data-submitname");A("<input>").attr({type:"hidden",id:Q+"input",name:Q,value:P}).appendTo("form");A(this).closest("form").submit();return false})});A('#pmform [name="move"]',N).click(function(P){A('#pmform [name="dowhat"]',N).val("move");A("#pmform",N).submit();P.preventDefault()});A('#pmform [name="delete"]',N).click(function(Q){var P=false;A('#pmform input[type="checkbox"]',N).each(function(){if(A(this).attr("checked")=="checked"){P=true}});if(P==true){if(confirm(vbphrase.are_you_sure_want_to_delete_selected_messages)){A('#pmform [name="dowhat"]',N).val("delete");A("#pmform",N).submit()}}else{alert(vbphrase.no_private_messages_selected)}Q.preventDefault()});A('button[name="moderate"]',N).click(function(P){A(".inlinemodcheckboxradio",N).toggle();A(".inlinemodbuttons",N).toggle();if(A(".inlinemodbuttons",N).is(":visible")){A('button[name="moderate"]',N).closest("div").attr("data-theme","e").removeClass("ui-btn-up-c").addClass("ui-btn-up-e").removeClass(" ui-btn-hover-c")}else{A('button[name="moderate"]',N).closest("div").attr("data-theme","c").removeClass("ui-btn-up-e").addClass("ui-btn-up-c").removeClass(" ui-btn-hover-e")}});A("button.inlinemod",N).unbind().click(function(Q){var P=A(this).attr("name");A("<input>").attr({type:"hidden",id:P+"input",name:"do",value:P}).appendTo("form");A(this).closest("form").submit();return false});A(".postbit",N).each(function(){var P=A(this).find("label.ui-btn");if(P.length){P.parent().addClass("inlinemodcheckboxradio");P.removeClass("ui-btn-corner-all");P.children("span").removeClass("ui-btn-corner-all")}});var J=D();A("a.multiquote",N).unbind().click(function(){I(A(this).attr("id").substr(3));return false}).each(function(){var P=A(this).attr("id").substr(3);M(P,(A.inArray(P,J)>-1))});function D(){var P=A.cookie("vbulletin_multiquote");if(P!=null&&P!=""){P=P.split(",")}else{P=new Array()}return P}function M(Q,P){if(P){A("#mq_"+Q,N).addClass("highlight");A("#mq_image_"+Q,N).attr("src",BBURL+"/"+IMGDIR_BUTTON+"/multiquote-selected_40b.png")}else{A("#mq_"+Q,N).removeClass("highlight");A("#mq_image_"+Q,N).attr("src",BBURL+"/"+IMGDIR_BUTTON+"/multiquote_40b.png")}}function I(U){var S=D();var Q=new Array();var T=false;for(R in S){if(!S.hasOwnProperty(R)){continue}if(S[R]==U){T=true}else{if(S[R]){Q.push(S[R])}}}M(U,!T);if(!T){Q.push(U);if(typeof mqlimit!="undefined"&&mqlimit>0){for(var R=0;R<(Q.length-mqlimit);R++){var P=Q.shift();M(P,false)}}}A.cookie("vbulletin_multiquote",Q.join(","),{path:"/"});return false}A(".bbcode_container a.view",N).click(function(R){var P=A(this).parents(".bbcode_container").eq(0).find(".bbcode_code");var Q=window.open("","bbcode_code");Q.document.write("<html><head><title>Popup</title></head><body>");Q.document.write('<p>[<a href="#" onclick="window.opener.focus();window.close();return false;">Back</a>]</p><div class="bbcode_container">');Q.document.write(P.outerHTML());Q.document.write("</div></body>");Q.document.close();return false});function F(R,S,P){var Q=/[\s$+,\/:=\?@"\'<>%{}|\\^~[\]`\r\n\t\x00-\x1f\x7f]/g;A(S).val(R.replace(Q,"-").replace(/(-+)/gi,"-").replace(/(^-|-$)/gi,""));A(P).val(R)}A("#cms_node_title",N).blur(function(){if(A("#cms_node_url",N).val()==""){F(A(this).val(),"#cms_node_url","#html_title")}})});A("[data-role=page]").live("pageshow",function(F){var D=this,C=window.location.href;var E=/do=(postreply|postthread)/gi;if(E.test(C)&&A(".postbit",D).length){setTimeout(function(){A.mobile.silentScroll(A(".postbit",D).last().position().top)},1200)}E=/#post(\d+)/gi;var G=E.exec(C);if(G){var H=G[1]||"";if(H){setTimeout(function(){A.mobile.silentScroll(A("#post_"+H,D).last().position().top)},1200)}}E=/&(new_comment|comments)/gi;if(E.test(C)&&A("div.comments",D).length){setTimeout(function(){A.mobile.silentScroll(A("div.comments",D).last().position().top)},1200)}})})(jQuery,this);(function(A,B){A.widget("mobile.imagereg",A.mobile.widget,{options:{theme:null},_create:function(){var D=this.element,E=this.options,C=this;D.find("#refresh_imagereg").removeClass("hidden").click(this,this.fetch_image);D.find("#imagereg").click(this,this.fetch_image);D.ajaxError(function(H,I,G,F){alert("error in: "+G.url+" \nerror:\n"+I.responseText)})},fetch_image:function(D){var C=D.data.element;D.preventDefault();C.find("#progress_imagereg").removeClass("hidden");A.ajax({type:"POST",url:"ajax.php?do=imagereg",data:SESSIONURL+"securitytoken="+SECURITYTOKEN+"&do=imagereg&hash="+C.find("#hash").val(),success:D.data.handle_ajax_response,dataType:"xml",context:D.data})},handle_ajax_response:function(D){var E=this.element;E.find("#progress_imagereg").addClass("hidden");if(D){var C=A(D).find("error").text();if(C){alert(C)}else{var F=A(D).find("hash").text();if(F){E.find("#hash").val(F);E.find("#imagereg").attr("src","image.php?"+SESSIONURL+"type=hv&hash="+F)}}}}})})(jQuery);(function(A,B){A.widget("mobile.vbpagenav",A.mobile.widget,{lowpage:1,highpage:1,numberoflinks:1,height:20,navbuttonwidth:20,pagenumberwidth:20,useqmark:false,options:{theme:null,pagenumber:1,totalpages:1,address:"",address2:"",anchor:""},_create:function(){var E=this.element,I=this.options,C=this;if(I.address.search(/\?/)==-1){this.useqmark=true}else{this.useqmark=false}var F=/\\?&?(page|pagenumber)=?\d+/gi;I.address=I.address.replace(F,"");A("<div>").addClass("ui-vbpagenav-pagenumbers").appendTo(E);var G=A("<a>",{href:I.address+I.address2+(this.useqmark?"?":"&")+"page=1"+(I.anchor?"#"+I.anchor:""),role:"button"}).text("First").addClass("ui-vbpagenav-first").appendTo(E).buttonMarkup({theme:I.theme,icon:"arrow-l",iconpos:"notext",corners:false,shadow:false,iconshadow:true}).click(function(){if(I.pagenumber==1){return false}return true});this.height=parseInt(G.css("height"))+parseInt(G.css("padding-top"))+parseInt(G.css("padding-bottom"))+parseInt(G.css("border-top-width"))+parseInt(G.css("border-bottom-width"));this.navbuttonwidth=parseInt(G.css("width"))+parseInt(G.css("padding-left"))+parseInt(G.css("padding-right"))+parseInt(G.css("border-left-width"))+parseInt(G.css("border-right-width"));A("<a>",{href:I.address+I.address2+(this.useqmark?"?":"&")+"page="+I.totalpages+(I.anchor?"#"+I.anchor:""),role:"button"}).text("Last").addClass("ui-vbpagenav-last").appendTo(E).buttonMarkup({theme:I.theme,icon:"arrow-r",iconpos:"notext",corners:false,shadow:false,iconshadow:true}).click(function(){if(I.pagenumber==I.totalpages){return false}return true});var H=E.find("div.ui-vbpagenav-pagenumbers");var D=A("<a>",{href:"#",role:"button"}).text("#").addClass("ui-vbpagenav-pagenumber").buttonMarkup({theme:I.theme,corners:false,inline:true,shadow:false,iconshadow:true}).addClass("ui-btn-icon-notext").appendTo(H);this.pagenumberwidth=parseInt(D.css("width"))+parseInt(D.css("padding-left"))+parseInt(D.css("padding-right"))+parseInt(D.css("border-left-width"))+parseInt(D.css("border-right-width"));D.remove();A("[data-role=page]").live("pageshow pagecreate orientationchange resize",function(){C.refresh()})},refresh:function(){var J=this.options,G=this.element,D=this;var I=G.find("div.ui-vbpagenav-pagenumbers");var C=A(document).width();this.numberoflinks=parseInt((C-this.navbuttonwidth*2)/this.pagenumberwidth)+1;this.lowpage=J.pagenumber-parseInt(this.numberoflinks*1.5)+1;if(this.lowpage<1){this.lowpage=1}this.highpage=J.pagenumber+parseInt(this.numberoflinks*1.5)+1;if(this.highpage>J.totalpages){this.highpage=J.totalpages}var F=C/2;var H=F-(J.pagenumber-this.lowpage+0.5)*this.pagenumberwidth;if(H>this.navbuttonwidth||((this.highpage-this.lowpage+1)*this.pagenumberwidth)<C-this.navbuttonwidth*2){H=this.navbuttonwidth}else{if((this.highpage-J.pagenumber+0.5)*this.pagenumberwidth+this.navbuttonwidth<F){H+=F-((this.highpage-J.pagenumber+0.5)*this.pagenumberwidth+this.navbuttonwidth)}}I.css("left",H.toString()+"px");I.empty();for(var E=this.lowpage;E<=this.highpage;E++){D._buildPagenumber(E,false)}I.live("swipeleft",function(){D.swipeleft()}).live("swiperight",function(){D.swiperight()});G.live("swipeleft",function(){D.swipeleft()}).live("swiperight",function(){D.swiperight()})},swipeleft:function(){var I=this.options,G=this.element,E=this;var H=G.find("div.ui-vbpagenav-pagenumbers");var D=A(document).width();var F,C;if((this.highpage-this.lowpage+1)*this.pagenumberwidth+H.offset().left>D-this.navbuttonwidth){C=this.highpage;this.highpage=this.highpage+this.numberoflinks-1;if(this.highpage>I.totalpages){this.highpage=I.totalpages}for(i=C+1;i<=this.highpage;i++){E._buildPagenumber(i,false)}F=(this.numberoflinks-2)*this.pagenumberwidth;if((this.highpage-this.lowpage+1)*this.pagenumberwidth+H.offset().left-F<D-this.navbuttonwidth){F=(this.highpage-this.lowpage+1)*this.pagenumberwidth+H.offset().left-D+this.navbuttonwidth}H.css("left",function(J,K){return(parseInt(K)-F).toString()+"px"})}},swiperight:function(){var H=this.options,E=this.element,C=this;var G=E.find("div.ui-vbpagenav-pagenumbers");var D,F;if(G.offset().left==this.navbuttonwidth){F=this.lowpage;this.lowpage=this.lowpage-this.numberoflinks+1;if(this.lowpage<1){this.lowpage=1}for(i=F-1;i>=this.lowpage;i--){C._buildPagenumber(i,true)}}D=(this.numberoflinks-2)*this.pagenumberwidth;if(G.offset().left+D>this.navbuttonwidth){D=this.navbuttonwidth-G.offset().left}G.css("left",function(I,J){if(D){return(parseInt(J)+D).toString()+"px"}return this.navbuttonwidth+"px"})},_buildPagenumber:function(F,D){var I=this.options,E=this.element,C=this;var H=E.find("div.ui-vbpagenav-pagenumbers");var G=A("<a>",{href:I.address+I.address2+(this.useqmark?"?":"&")+"page="+F+(I.anchor?"#"+I.anchor:""),role:"button"}).text(F.toString()).addClass("ui-vbpagenav-pagenumber").buttonMarkup({theme:I.theme,corners:false,inline:true,shadow:false,iconshadow:true}).addClass("ui-btn-icon-notext").live("swipeleft",function(){C.swipeleft()}).live("swiperight",function(){C.swiperight()});if(D){G.prependTo(H)}else{G.appendTo(H)}if(F==I.pagenumber){G.removeClass("ui-btn-up-d").addClass("ui-btn-up-e").click(function(){return false})}}})})(jQuery);(function(A,C){var B={};A.widget("mobile.listview",A.mobile.listview,{_itemApply:function(E,F){var D=this;var G=F.find(".ui-li-count");if(G.length){F.addClass("ui-li-has-count")}G.addClass("ui-btn-up-"+(E.jqmData("counttheme")||this.options.countTheme)+" ui-btn-corner-all");F.find("h1, h2, h3, h4, h5, h6").addClass("ui-li-heading").end().find("p, dl").addClass("ui-li-desc").end().find(">img:eq(0), .ui-btn-text>img:eq(0)").addClass("ui-li-thumb").each(function(){A(this).closest("li").addClass(A(this).is(".ui-li-icon")?"ui-li-has-icon":"ui-li-has-thumb");if(!A(this).hasClass("ui-li-icon")){A(this).one("load",function(){D._refreshthumb(this)}).each(function(){if(this.complete){A(this).trigger("load")}})}}).end().find(".ui-li-aside").each(function(){var H=A(this);H.prependTo(H.parent())})},refresh:function(P){this.parentPage=this.element.closest(".ui-page");this._createSubPages();var T=this.options,D=this.element,S=this,X=D.jqmData("dividertheme")||T.dividerTheme,Q=D.jqmData("splittheme"),I=D.jqmData("spliticon"),R=D.children("li"),N=A.support.cssPseudoElement||!A.nodeName(D[0],"ol")?0:1,V,E,O,W,M,H,F,U;if(N){D.find(".ui-li-dec").remove()}for(var K=0,G=R.length;K<G;K++){V=R.eq(K),E="ui-li";if(P||!V.hasClass("ui-li")){O=V.jqmData("theme")||T.theme,W=V.find("a");if(W.length){U=V.jqmData("icon");V.buttonMarkup({wrapperEls:"div",shadow:false,corners:false,iconpos:"right",icon:"arrow-r",theme:O});if((U!=false)&&(W.length==1)){V.addClass("ui-li-has-arrow")}W.first().addClass("ui-link-inherit");W.each(function(Y){if(A(this).hasClass("splitlink")){E+=" ui-li-has-alt";M=A(this),H=Q||M.jqmData("theme")||T.splitTheme;M.appendTo(V).attr("title",M.text()).addClass("ui-li-link-alt").empty().buttonMarkup({shadow:false,corners:false,theme:O,icon:false,iconpos:false}).find(".ui-btn-inner").append(A("<span />").buttonMarkup({shadow:true,corners:true,theme:H,iconpos:"notext",icon:I||M.jqmData("icon")||T.splitIcon}))}})}else{if(V.jqmData("role")==="list-divider"){E+=" ui-li-divider ui-btn ui-bar-"+X;V.attr("role","heading");if(N){N=1}}else{E+=" ui-li-static ui-body-"+O}}D.find("li > .ui-btn-inner").bind("click",function(Z){var Y=A(this).find("a").first().attr("href");if(Y){var a=/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;if(!a.test(Y)){Y=BBURL+"/"+Y}window.top.location=Y}return false});var J=V.find("[type='radio'], [type='checkbox']");if(J.length){var L=A("<label>").attr("for",J.attr("id"));J.add(L).insertBefore(A(">.ui-btn-inner",V));J.checkboxradio();J.parent().addClass("inlinemodcheckboxradio");L.removeClass("ui-btn-corner-all");L.children("span").removeClass("ui-btn-corner-all");E+=" ui-li-checkboxradio"}}if(T.inset){if(K===0){E+=" ui-corner-top";V.add(V.find(".ui-btn-inner")).find(".ui-li-link-alt").addClass("ui-corner-tr").end().find(".ui-li-thumb").addClass("ui-corner-tl");if(V.next().next().length){S._removeCorners(V.next())}}if(K===R.length-1){E+=" ui-corner-bottom";V.add(V.find(".ui-btn-inner")).find(".ui-li-link-alt").addClass("ui-corner-br").end().find(".ui-li-thumb").addClass("ui-corner-bl");if(V.prev().prev().length){S._removeCorners(V.prev())}}}if(N&&E.indexOf("ui-li-divider")<0){F=V.is(".ui-li-static:first")?V:V.find(".ui-link-inherit");F.addClass("ui-li-jsnumbering").prepend("<span class='ui-li-dec'>"+(N++)+". </span>")}V.add(V.find(".ui-btn-inner")).addClass(E);S._itemApply(D,V)}this._refreshCorners(P)},_createSubPages:function(){var J=this.element,K=J.closest(".ui-page"),D=K.jqmData("url"),G=D||K[0][A.expando],E=J.attr("id"),F=this.options,H="data-"+A.mobile.ns,L=this,I=K.find(":jqmData(role='footer')").jqmData("id");if(typeof (B[G])==="undefined"){B[G]=-1}E=E||++B[G];A(J.find("li>ul, li>ol").toArray().reverse()).each(function(N){var Q=A(this),P=Q.attr("id")||E+"-"+N,S=Q.parent(),U=A(Q.prevAll().toArray().reverse()),U=U.length?U:A("<span>"+A.trim(S.contents()[0].nodeValue)+"</span>"),R=U.first().text(),M=(D||"")+"&"+A.mobile.subPageUrlKey+"="+P;theme=Q.jqmData("theme")||F.theme,countTheme=Q.jqmData("counttheme")||J.jqmData("counttheme")||F.countTheme,newPage=Q.detach().wrap("<div "+H+"role='page' "+H+"url='"+M+"' "+H+"theme='"+theme+"' "+H+"count-theme='"+countTheme+"'><div "+H+"role='content'></div></div>").parent().before("<div "+H+"role='header' "+H+"theme='"+F.headerTheme+"'><div class='ui-title'>"+R+"</div></div>").after(I?A("<div "+H+"role='footer' "+H+"id='"+I+"'>"):"").parent().appendTo(A.mobile.pageContainer);newPage.page();var O=S.find("a:first");if(!O.length){O=A("<a />").html(U||R).prependTo(S.empty());O.attr("href",window.top.location+"#"+M)}else{var T=A("<a>").attr("href",window.top.location+"#"+M).addClass("splitlink").click(function(){window.top.location=window.top.location+"#"+M;return false}).appendTo(S)}}).listview()},refreshthumbs:function(){var G=this.options,F=this.element,E=this,D=F.children("li");D.each(function(I){var H=A(this);H.find("img.ui-li-thumb").each(function(){E._refreshthumb(this)})})},_refreshthumb:function(D){A(D).removeAttr("width").removeAttr("height").css({width:"",height:""});if(D.width>D.height&&D.width>=80){D.height=D.height*80/D.width;D.width=80}else{if(D.width<=D.height&&D.height>=80){D.width=D.width*80/D.height;D.height=80}}if(D.width<=80){A(D).css("left",((100-D.width)/2)+"px")}if(D.height<=80){var E=D;setTimeout(function(){var F=A(E).parents(".ui-btn-inner").first().innerHeight();A(E).css("top",((F-E.height)/2)+"px")},80)}}})})(jQuery);jQuery.fn.outerHTML=function(A){return(A)?this.before(A).remove():jQuery("<p>").append(this.eq(0).clone()).html()};jQuery.cookie=function(B,I,L){if(typeof I!="undefined"){L=L||{};if(I===null){I="";L.expires=-1}var E="";if(L.expires&&(typeof L.expires=="number"||L.expires.toUTCString)){var F;if(typeof L.expires=="number"){F=new Date();F.setTime(F.getTime()+(L.expires*24*60*60*1000))}else{F=L.expires}E="; expires="+F.toUTCString()}var K=L.path?"; path="+(L.path):"";var G=L.domain?"; domain="+(L.domain):"";var A=L.secure?"; secure":"";document.cookie=[B,"=",encodeURIComponent(I),E,K,G,A].join("")}else{var D=null;if(document.cookie&&document.cookie!=""){var J=document.cookie.split(";");for(var H=0;H<J.length;H++){var C=jQuery.trim(J[H]);if(C.substring(0,B.length+1)==(B+"=")){D=decodeURIComponent(C.substring(B.length+1));break}}}return D}};