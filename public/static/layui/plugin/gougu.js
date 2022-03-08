layui.define(['layer'], function(exports){
    var layer = layui.layer;
	    var obj = {
		loading:false,
        open: function (url='',width='88%') {
			let that=this;
			if(that.loading==true){
				return false;
			}
			that.loading=true;
			that.get(url,{},function(res){
				layer.open({
					type: 1,
					title: '',
					offset: ['0', '100%'],
					skin: 'layui-anim layui-anim-rl layui-layer-admin-right',
					closeBtn: 0,
					content: res,
					area: [width, '100%'],
					success:function(obj,index){
						that.loading=false;
						pageInit();
						$('body').addClass('right-open');						
						let btn='<div id="rightPopup'+index+'" class="right-popup-close" title="关闭">关闭</div>';
						obj.append(btn);
						$('#rightPopup'+index).click(function(){
							let op_width = $('.layui-anim-rl').outerWidth();
							$('.layui-anim-rl').animate({left:'+='+op_width+'px'}, 200, 'linear', function () {
								$('.layui-anim-rl').remove()
								$('.layui-layer-shade').remove()
							})
							$('body').removeClass('right-open');
						})
					}
				})
			})            
        },
		close: function(){
			$('.right-popup-close').click();
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