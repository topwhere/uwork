layui.define(['layer','gougu','employeepicker'], function(exports){
    var layer = layui.layer;
    var table = layui.table;
    var laydate = layui.laydate;
    var gougu = layui.gougu;
    var employeepicker = layui.employeepicker;
	var obj = {
		loading:false,
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
				area: ['500px', '500px'],
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
								field: 'name',
								title: '选项'
							}]
						],
						data: data
					});
				},
				btn:['确定'],
				btnAlign: 'c',
				yes: function() {
					var checkStatus = table.checkStatus(selectable.config.id);
					var data = checkStatus.data;
					if (data.length > 0) {
						that.post(id,name,data[0].name,data[0].id);
					}
					else{
						layer.msg('请选择');
					}
				}
			})
		},
		date:function(id,name,real_txt){
			let that=this;
			console.log(real_txt);
			laydate.render({
				elem: '#'+name+'_'+id
				,showBottom: false
				,show: true //直接显示
				,value:real_txt
				,done: function(value, date){
					that.post(id,name,value,value);
				}
			});
		},
		editor:function(id,name,real_txt){
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
			if(that.loading==true){
				return false;
			}
			if(name=="name" || name=="code"){
				that.text(id,name,real_txt);
			}
			if(name=="start_time" || name=="end_time"){
		
				that.date(id,name,real_txt);
			}
			if(name =="director_uid"){
				that.employee_one(id,name,show_txt,real_txt);
			}
			if(name =="team_admin_ids"){
				that.employee_more(id,name,show_txt,real_txt);
			}
			if(name=="product_id"){		
				that.loading=true;
				gougu.get("/api/index/get_product",{},function(res){
					let data = res.data;
					that.loading=false;
					that.select_type(id,name,real_txt,data);
				});				
			}
			if(name=="md_content"){
				that.editor(id,name,real_txt);
			}
        },
		post:function(id,name,show_val,real_val){
			let callback = function (e) {
				layer.msg(e.msg);
				if (e.code == 0) {
					$('#'+name+'_'+id).html(show_val).data('val',real_val);
					$('#editTips').html('，最近更新于 5秒前');
				}
				setTimeout(function(){
					layer.closeAll();
				},1000)				
			}
			let postData={id:id};
			postData[name] = real_val;
			if(name=='md_content'){
				postData['content'] = show_val;
			}
			gougu.post("/project/index/add",postData,callback);
		}
    };
    exports('projectEdit',obj);
});  