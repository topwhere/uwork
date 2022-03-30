layui.define(['layer'], function(exports){
    var layer = layui.layer;
	var obj = {
		loading:false,
		editor:function(editormdId,editormdHeight,editormdMarkdown){
			layui.Editor = editormd(editormdId, {
				placeholder : "此处开始编写您要发布的内容...",//这里不设置则为默认的
				path : '/static/mdeditor/lib/',
				height           : editormdHeight,
				markdown         : editormdMarkdown,
				htmlDecode       : "style,script,iframe",
				watch            : false, 
				imageUpload: true,
				imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
				imageUploadURL: "/api/index/md_upload",		
				saveHTMLToTextarea : true,//保存html到textarea
				toolbarIcons : function() {
					return [
						"undo", "redo","bold", "del", "italic", "quote","h3",
						"list-ul", "list-ol", "hr","link", "reference-link", "image", "code", "table","watch"
					];
				}, 
				lang:{
					dialog:{
						preformattedText:{placeholder : "此处编写代码..."},
						codeBlock:{placeholder : "此处编写代码...."}
					}
				},
				onfullscreen : function() {
					this.editor.css("z-index", 120);
				},
				onfullscreenExit : function() {
					this.editor.css({
						zIndex : 10,
						border : "none",
					});			
					this.resize();
				}
			})
		},
        open: function (url='',width=0) {
			let that=this;
			if(that.loading==true){
				return false;
			}
			that.loading=true;
			if(width==0){
				width = window.innerWidth>1280?'1220px':'1080px';
			}
			$.ajax({
				url:url,
				type:"GET",
				timeout:10000,
				success:function(res){
					if(res['code'] && res['code']>0){
						layer.msg(res.msg);
						return false;
					}
					var express='<section id="expressLayer" class="express-box" style="width:'+width+'"><article id="articleLayer">'+res+'</article><div id="expressClose" class="express-close" title="关闭">关闭</div></section><div id="expressMask" class="express-mask"></div>';
					
					$('body').append(express).addClass('right-open');	
					$('#expressMask').fadeIn(200);
					$('#expressLayer').animate({'right': 0}, 200, 'linear', function () {
						openInit();
					});
					
					$('#expressClose').click(function(){
						$('#expressMask').fadeOut(100);
						$('#expressLayer').animate({'right': '-100%'}, 100, 'linear', function () {
							$('#expressLayer').remove();
							$('#expressMask').remove();							
						})
					})
					$(window).resize(function () {
						width = window.innerWidth>1280?'1220':'1080';
						$('#expressLayer').width(width);
					})
				}
				,error:function(xhr,textstatus,thrown){
					console.log('错误');
				},
				complete:function(){
					that.loading=false;
				}
			});          
        },
		load:function(url){
			let that=this;
			if(that.loading==true){
				return false;
			}
			that.loading=true;
			$.ajax({
				url:url,
				type:"GET",
				timeout:10000,
				success:function(res){
					if(res['code'] && res['code']>0){
						layer.msg(res.msg);
						return false;
					}
					$('#articleLayer').html(res);
					openInit();
				}
				,error:function(xhr,textstatus,thrown){
					console.log('错误');
				},
				complete:function(){
					that.loading=false;
				}
			});
		},
		page:function(url){
			let that=this;
			if(that.loading==true){
				return false;
			}
			that.loading=true;
			$.ajax({
				url:url,
				type:"GET",
				timeout:10000,
				success:function(res){
					if(res['code'] && res['code']>0){
						layer.msg(res.msg);
						return false;
					}
					$('#pageBox').html(res);
					pageInit();
				}
				,error:function(xhr,textstatus,thrown){
					console.log('错误');
				},
				complete:function(){
					that.loading=false;
				}
			});
		},
		close: function(){
			$('#expressClose').click();
		},
		get: function (url,data,callback){
			$.ajax({
				url:url,
				type:"GET",
				timeout:10000,
				data:data,
				success:function(res){
					callback(res);
				}
				,error:function(xhr,textstatus,thrown){
					console.log('错误');
				}
			});
		},
		post: function(url,data,callback){
			$.ajax({
				url:url,
				type:"POST",
				data:data,
				timeout:10000,
				success:function(res){
					callback(res);
				},
				error:function(xhr,textstatus,thrown){
					console.log('错误');
				}
			});
		},
		put: function(url,data,callback){
			$.ajax({
				url:url,
				type:"PUT",
				data:data,
				timeout:10000,
				success:function(res){
					callback(res);
				},
				error:function(xhr,textstatus,thrown){
					console.log('错误');
				}
			});
		},
		delete: function(url,data,callback){
			$.ajax({
				url:url,
				type:"DELETE",
				data:data,
				success:function(res){
					callback(res);
				},
				error:function(xhr,textstatus,thrown){
					console.log('错误');
				}
			});
		}
    };
    exports('gougu',obj);
});  