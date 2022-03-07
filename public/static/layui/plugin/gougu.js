layui.define(['layer'], function(exports){
    var layer = layui.layer;
	    var obj = {
		callback:null,
        open: function (content='',width='88%',callback) {
			if(callback && typeof callback === 'function'){
				this.callback = callback;
			}
			this.getJSON(content,{},function(res){
				layer.open({
					type: 1,
					title: '',
					offset: ['0', '100%'],
					skin: 'layui-anim layui-anim-rl layui-layer-admin-right',
					closeBtn: 0,
					content: res,
					area: [width, '100%'],
					success:function(obj,index){
						pageInit();
						$('body').addClass('right-open');
						if($('#rightPopup'+index).length<1){
							var btn='<div id="rightPopup'+index+'" class="right-popup-close" title="关闭">关闭</div>';
							obj.append(btn);
							$('#rightPopup'+index).click(function(){
								var op_width = $('.layui-anim-rl').outerWidth();
								$('.layui-anim-rl').animate({left:'+='+op_width+'px'}, 200, 'linear', function () {
									$('.layui-anim-rl').remove()
									$('.layui-layer-shade').remove()
								})
								$('body').removeClass('right-open');
							})
						}
					}
				})
			})            
        },
		success:function(){
			$('.right-popup-close').click();
			var d = this;
			setTimeout(function() {
				d.timer = null;
				d.callback && d.callback();
			}, 300)
		},
		close: function(){
			$('.right-popup-close').click();
		},
		getJSON: function (url,data,callback){
			$.ajax({
				url:url,
				type:"get",
				timeout:10000,
				data:data,
				success:function(data){
					callback(data);
				}
				,error:function(xhr,textstatus,thrown){
					console.log('错误');
				}
			});
		},
		postJSON: function(url,data,callback){
			$.ajax({
				url:url,
				type:"post",
				data:data,
				timeout:10000,
				success:function(msg){
					callback(msg);
				},
				error:function(xhr,textstatus,thrown){
					console.log('错误');
				}
			});
		},
		putJSON: function(url,data,callback){
			$.ajax({
				url:url,
				type:"put",
				data:data,
				timeout:10000,
				success:function(msg){
					callback(msg);
				},
				error:function(xhr,textstatus,thrown){
					console.log('错误');
				}
			});
		},
		deleteJSON: function(url,data,callback){
			$.ajax({
				url:url,
				type:"delete",
				data:data,
				success:function(msg){
					callback(msg);
				},
				error:function(xhr,textstatus,thrown){

				}
			});
		}
    };
	
    $(window).resize(function () {
		
    })

    exports('gougu',obj);
});  