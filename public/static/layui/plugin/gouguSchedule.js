layui.define(['gougu'], function(exports){
    const layer = layui.layer;
    const form = layui.form;
    const gougu = layui.gougu;
    const laydate = layui.laydate;
	const obj = {
		load:function(tid){
			let callback = function(res){
				if(res.code==0 && res.data.length>0){
					let itemSchedule = '';					
					$.each(res.data, function (index, item) {
						itemSchedule+= `
							<div class="work-record py-1">
								<p class="font-gray">${item.start_time}~${item.end_time}，${item.labor_time}工时</p>
								<p>${item.name}：${item.title}</p>
							</div>
						`;
					});
					$("#schedule_"+tid).html(itemSchedule);
					layer.closeAll();
				}
			}
			gougu.post("/home/schedule/get_list",{tid:tid},callback);
		},
		//保存
		save:function(postData){
			let that=this;
			let callback = function(e){
				layer.msg(e.msg);
				if(e.code==0){															
					gougu.load('/task/index/view?id='+postData.tid);
					setTimeout(function(){
						layer.closeAll();
					},1000)
				}				
			}
			gougu.post("/home/schedule/add",postData,callback);
		},		
		//编辑
		edit:function(data){
			var that = this,detail={};
			detail['id']=data.id;
			detail['tid']=data.tid;
			detail['title']=data.title;
			detail['remark']=data.remark;
			var content=`<form class="layui-form" style="width:776px">
							<table class="layui-table" style="margin:12px 12px 0;">
								<tr>
									<td class="layui-td-gray2">工作时间范围 <span style="color: red">*</span></td>
									<td>${data.start_time} ${data.start_time_1} 至 '${data.end_time_1}</td>
								</tr>
								<tr>
									<td class="layui-td-gray2">工作内容 <span style="color: red">*</span></td>
									<td><input name="title" class="layui-input" value="${detail.title}" lay-verify="required" lay-reqText="请完成工作内容"></td>
								</tr>
								<tr>
									<td class="layui-td-gray2">工作详细描述</td>
									<td>
										<textarea name="remark" form-input="remark" class="layui-textarea" style="min-height:120px;">${detail.remark}</textarea>
									</td>
								</tr>
							</table>
						</form>`;
			layer.open({
				type:1,
				title:'编辑工作记录',
				area:['900px','390px'],
				content:content,
				success:function(){				
					$('[name="title"]').on('input',function(){
						var _val=$(this).val();
						detail.title=_val;
					});	
					$('[form-input="remark"]').on('input',function(){
						var _val=$(this).val();
						detail.remark=_val;
					});					
				},
				btn: ['确定提交'],
				btnAlign:'c',
				yes: function(idx){
					if(detail.title==''){
						layer.msg('请填写工作内容');
						return;
					}
					that.save(detail);
				}
			})	
		},		
		//查看
		view:function(detail){
			var content=`<div style="width:776px">
							<table class="layui-table" style="margin:12px 12px 0;">
								<tr>
									<td class="layui-td-gray">执行人</td>
									<td>${detail.user}</td>
								</tr>
								<tr>
									<td class="layui-td-gray2">工作内容</td>
									<td>${detail.title}</td>
								</tr>
								<tr>
									<td class="layui-td-gray2">工作时间范围</td>\
									<td>${detail.start_time} ${detail.start_time_1} 至 ${detail.end_time_1}，共${detail.labor_time}工时</td>
								</tr>
								<tr>
									<td class="layui-td-gray2">工作描述</td>
									<td>${detail.remark}</td>
								</tr>
							</table>
						</div>`;
			layer.open({
				type:1,
				title:'查看工作记录',
				area:['800px','336px'],
				content:content,
				success:function(){
					
				},
				btn: ['关闭'],
				btnAlign: 'c',
				yes: function(idx){
					layer.close(idx);			
				}
			})	
		},		  
		add:function(tid){
			var that = this,detail={};
			detail['id']=0;
			detail['tid']=tid;
			detail['title']='';
			detail['start_time_a']='';
			detail['end_time_a']='';		
			detail['start_time_b']='09:00';
			detail['end_time_b']='09:30';		
			detail['remark']='';
			var content=`<form class="layui-form" style="width:700px">
							<table class="layui-table" style="margin:12px 12px 0;">
								<tr>
									<td class="layui-td-gray2">工作时间范围 <span style="color: red">*</span></td>
									<td>
										<input id="start_time_a" name="start_time_a" style="width:150px; display:inline-block;" autocomplete="off" class="layui-input" value="" readonly lay-verify="required" lay-reqText="请选择"><div style="display: inline-block; margin-left:3px; width: 100px;"><select lay-filter="start_time_b" id="start_time_b"></select></div> 至 <input id="end_time_a" name="end_time_a" style="width:150px; display:inline-block;" autocomplete="off" class="layui-input" value="" readonly lay-verify="required" lay-reqText="请选择"><div style="display: inline-block; margin-left:3px; width: 100px;"><select lay-filter="end_time_b" id="end_time_b"></select></div>
									</td>
								</tr>
								<tr>
									<td class="layui-td-gray2">工作内容 <span style="color: red">*</span></td>
									<td><input name="title" class="layui-input" value="" lay-verify="required" lay-reqText="请完成工作内容"></td>
								</tr>
								<tr>
									<td class="layui-td-gray2">详细描述</td>
									<td>
										<textarea name="remark" form-input="remark" class="layui-textarea" style="min-height:120px;"></textarea>
									</td>
								</tr>
							</table>
						</form>`;
			layer.open({
				type:1,
				title:'添加工作记录',
				area:['724px','388px'],
				content:content,
				success:function(){
					//日期时间范围
					laydate.render({
						elem: '#start_time_a',
						type: 'date',
						min: -7,
						max:0,
						format: 'yyyy-MM-dd',
						showBottom: false,
						done:function(a,b,c){
							$('#end_time_a').val(a);
							detail.start_time_a=a;
							detail.end_time_a=a;
						}
					});

					//日期时间范围
					laydate.render({
						elem: '#end_time_a',
						type: 'date',
						min: -7,
						max:0,
						format: 'yyyy-MM-dd',
						showBottom: false,
						done:function(a,b,c){
							$('#start_time_a').val(a);
							detail.start_time_a=a;
							detail.end_time_a=a;
						}
					});
					$('#start_time_b,#end_time_b').empty();
					
					var hourArray=[];
					for(var h=0;h<24;h++){
						var t=h<10?'0'+h:h
						var t_1=t+':00',t_2=t+':15',t_3=t+':30',t_4=t+':45';
						hourArray.push(t_1,t_2,t_3,t_4);
					}
					
					var html_1='', html_2='',def_h1=detail['start_time_b'],def_h2=detail['end_time_b'];
					for(var s=0;s<hourArray.length;s++){
						var check_1='',check_2='';
						if(hourArray[s]==def_h1){
							check_1='selected';
						}
						if(hourArray[s]==def_h2){
							check_2='selected';
						}
						html_1 += '<option value="'+hourArray[s]+'" '+check_1+'>'+hourArray[s]+'</option>';
						html_2 += '<option value="'+hourArray[s]+'" '+check_2+'>'+hourArray[s]+'</option>';
					}
					
					$('#start_time_b').append(html_1);
					$('#end_time_b').append(html_2);
					form.render();
					
					$('[name="title"]').on('input',function(){
						var _val=$(this).val();
						detail.title=_val;
					});	
					form.on('select(start_time_b)', function(data){
						detail.start_time_b=data.value;
					});
					form.on('select(end_time_b)', function(data){
						detail.end_time_b=data.value;
					});
					$('[form-input="remark"]').on('input',function(){
						var _val=$(this).val();
						detail.remark=_val;
					});					
				},
				btn: ['确定提交'],
				btnAlign:'c',
				yes: function(idx){
					if(detail.start_time_a=='' || detail.end_time_a==''){
						layer.msg('请选择工作时间范围');
						return;
					}
					if(detail.title==''){
						layer.msg('请填写工作内容');
						return;
					}
					that.save(detail);			
				}
			})	
		}
    };
    exports('gouguSchedule',obj);
});  