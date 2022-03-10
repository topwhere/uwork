layui.define(['layer','gougu','employeepicker'], function(exports){
    var layer = layui.layer;
    var table = layui.table;
    var gougu = layui.gougu;
    var employeepicker = layui.employeepicker;
	var obj = {
		text:function(id,name,real_txt){
			let that=this;
			layer.prompt({
				title: '请输入内容',
				value: real_txt,
				yes: function(index, layero) {
					// 获取文本框输入的值
					let newval = layero.find(".layui-layer-input").val();
					if (newval) {
						that.post(id,name,newval,newval);
					} else {
						layer.msg('请输入内容');
					}
				}
			})
		},
		employee_one:function(id,name,show_txt,real_txt){
			let that=this;
			employeepicker.init({
				ids:real_txt.toString(),
				names:show_txt,
				department_url: "/api/index/get_department_tree",
				employee_url: "/api/index/get_employee",
				type:0,
				callback:function(ids,names){
					that.post(id,name,names,ids);
				}
			});
		},
		employee_more:function(id,name,show_txt,real_txt){
			let that=this;
			let ids=[];
			let names = [];
			if(real_txt.length>0){
				ids=real_txt.toString().split(',');
				names = show_txt.split(',');
			}
			employeepicker.init({
				ids:ids,
				names:names,
				department_url: "/api/index/get_department_tree",
				employee_url: "/api/index/get_employee",
				type:1,
				callback:function(ids,names){
					that.post(id,name,names.join(','),ids.join(','));
				}
			});				
		},
		select_type:function(id,name,real_val,data){
			let that=this;
			layer.open({
				title: '请选择',
				type:1,
				area: ['500px', '360px'],
				content: '<div style="padding:16px 16px 0"><div id="selectBox"></div></div>',
				success: function() {
					selectable = table.render({
						elem: '#selectBox',
						cols: [
							[{
								type: 'radio',
								title: '选择',
								width: 100
							}, {
								field: 'title',
								title: '选项'
							}]
						],
						data: data
					});
				},
				btn:['确定'],
				yes: function() {
					var checkStatus = table.checkStatus(selectable.config.id);
					var data = checkStatus.data;
					if (data.length > 0) {
						that.post(id,name,data[0].title,data[0].id);
					}
					else{
						layer.msg('请选择');
					}
				}
			})
		},
		editor:function(id,name,real_txt){
			console.log(real_txt);
			let that=this;
			layer.open({
				closeBtn: 2,
				title: false,
				type:1,
				area: ['960px', '560px'],
				content: '<div style="padding:10px 16px 0 12px;"><div id="editorBox"></div></div>',
				success: function() {
					buildEditor('editorBox',468,real_txt);
				},
				btnAlign: 'c',
				btn:['确定'],
				yes: function() {
					that.post(id,name,markdownEditor.getHTML(),markdownEditor.getMarkdown());
				}
			})			
		},
        show: function (id,name,show_txt,real_txt) {
			let that=this;
			if(name=='name'){
				that.text(id,name,real_txt);
			}
			if(name =="director_uid" || name =="test_uid"){
				that.employee_one(id,name,show_txt,real_txt);
			}
			if(name =="check_admin_ids" || name =="view_admin_ids"){
				that.employee_more(id,name,show_txt,real_txt);
			}
			if(name=='is_open'){
				let data = [
					{'id':1,'title':'私有<span class="font-gray">(白名单员工可访问者)</span>'},
					{'id':2,'title':'公开<span class="font-gray">(有产品视图权限员工均可访问)</span>'}
				];
				that.select_type(id,name,real_txt,data);
			}
			if(name=='content'){
				let data = [
					{'id':1,'title':'私有<span class="font-gray">(白名单员工可访问者)</span>'},
					{'id':2,'title':'公开<span class="font-gray">(有产品视图权限员工均可访问)</span>'}
				];
				that.editor(id,name,real_txt);
			}
        },
		post:function(id,name,show_val,real_val){
			let callback = function (e) {
				layer.msg(e.msg);
				if (e.code == 0) {
					$('#'+name+'_'+id).html(show_val).data('val',real_val);
					$('#editTips').html('，最近更新于 5秒前');
					if(name=='is_open' && real_val==1){
						$('#is_open_son').show();
					}
					if(name=='is_open' && real_val==2){
						$('#is_open_son').hide();
					}
				}
				setTimeout(function(){
					layer.closeAll();
				},2000)				
			}
			let postData={id:id};
			postData[name] = show_val;
			if(name=='content'){
				postData['md_content'] = real_val;
			}
			gougu.post("/product/index/add",postData,callback);
		}
    };
    exports('productEdit',obj);
});  