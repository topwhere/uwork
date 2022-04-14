layui.define(['gougu'], function(exports){
    const layer = layui.layer,gougu = layui.gougu;
	const obj = {
		log:function(topic_id,module){
			let callback = function(res){
				if(res.code==0 && res.data.length>0){
					let itemLog = '';					
					$.each(res.data, function (index, item) {
						if(item.field =='content'){
							itemLog+= `
							<div class="log-item py-3 border-b">
								<i class="iconfont ${item.icon}"></i>
								<span class="log-name">${item.name}</span>
								<span class="log-content font-gray"> ${item.action}了<strong>${item.title}</strong><i title="对比查看" class="iconfont icon-yuejuan" style="color:#1E9FFF; cursor: pointer;"></i> <span class="font-gray" title="${item.create_time}">${item.times}</span></span>
							</div>
						`;
						}
						else if(item.field =='file'){
							itemLog+= `
								<div class="log-item py-3 border-b">
									<i class="iconfont ${item.icon}"></i>
									<span class="log-name">${item.name}</span>
									<span class="log-content font-gray"> ${item.action}了${item.title}<strong>${item.new_content}</strong><span class="font-gray" title="${item.create_time}">${item.times}</span></span>
								</div>
							`;
						}else if(item.field =='new' || item.field =='delete'){
							itemLog+= `
								<div class="log-item py-3 border-b">
									<i class="iconfont ${item.icon}"></i>
									<span class="log-name">${item.name}</span>
									<span class="log-content font-gray"> ${item.action}了<strong>${item.title}</strong><span class="font-gray" title="${item.create_time}">${item.times}</span></span>
								</div>
							`;
						}
						else if(item.field =='document'){
							if(item.action =='修改'){
								itemLog+= `
									<div class="log-item py-3 border-b">
										<i class="iconfont ${item.icon}"></i>
										<span class="log-name">${item.name}</span>
										<span class="log-content font-gray"> ${item.action}了${item.title}<strong>${item.remark}</strong><i title="对比查看" class="iconfont icon-yuejuan" style="color:#1E9FFF; cursor: pointer;"></i> <span class="font-gray" title="${item.create_time}">${item.times}</span></span>
									</div>
								`;
							}
							else{
								itemLog+= `
									<div class="log-item py-3 border-b">
										<i class="iconfont ${item.icon}"></i>
										<span class="log-name">${item.name}</span>
										<span class="log-content font-gray"> ${item.action}了${item.title}<strong>${item.remark}</strong><span class="font-gray" title="${item.create_time}">${item.times}</span></span>
									</div>
								`;
							}
						}
						else{
						itemLog+= `
							<div class="log-item py-3 border-b">
								<i class="iconfont ${item.icon}"></i>
								<span class="log-name">${item.name}</span>
								<span class="log-content font-gray"> 将<strong>${item.title}</strong>从 ${item.old_content} ${item.action}为<strong>${item.new_content}</strong><span class="font-gray" title="${item.create_time}">${item.times}</span></span>
							</div>
						`;
						}
					});
					$("#log_"+module+"_"+topic_id).html(itemLog);
				}
			}
			gougu.get("/api/log/get_list",{tid:topic_id,m:module},callback);
		},
		load:function(topic_id,module){
			let callback = function(res){
				if(res.code==0 && res.data.length>0){
					let itemComment = '';					
					$.each(res.data, function (index, item) {
						let pAdmin='',ops='';
						if(item.padmin_id>0){
							pAdmin = '<p class="pt-2"><span>@'+item.pname+'</span></p>';
						}
						if(item.admin_id == GOUGU_DEV.uid){
							ops='<a class="mr-4" data-event="edit" data-id="'+item.id+'">编辑</a><a class="mr-4" data-event="del" data-id="'+item.id+'">删除</a>';
						}
						itemComment+= `
							<div id="comment_${item.id}" class="comment-item py-3 border-t" data-mdcontent="${item.md_content}">
							<div class="comment-avatar" title="${item.name}">
								<img class="comment-image" src="${item.thumb}">
							</div>
							<div class="comment-body">
								<div class="comment-meta">
									<strong class="comment-name">${item.name}</strong><span class="ml-2 font-gray" title="${item.create_time}">${item.times}${item.update_time}</span>
								</div>
								<div class="comment-content py-2">
									${item.content}${pAdmin}									
								</div>
								<div class="comment-actions">
									<a class="mr-4" data-event="replay" data-id="${item.id}" data-uid="${item.admin_id}">回复</a>${ops}
								</div>
							</div>
						</div>
						`;
					});
					$("#comment_"+module+"_"+topic_id).html(itemComment);
					layer.closeAll();
				}
			}
			gougu.post("/api/comment/get_list",{tid:topic_id,m:module},callback);
		},
		add:function(id,topic_id,pid,padmin_id,module,content,md_content){
			let that=this;
			let callback = function(res){
				that.load(topic_id,module);
			}
			let postData={id:id,topic_id:topic_id,pid:pid,padmin_id:padmin_id,module:module,content:content,md_content:md_content};
			gougu.post("/api/comment/add",postData,callback);			
		},
		del:function(id,topic_id,module){
			let that=this;
			layer.confirm('确定删除该评论吗？', {
				icon: 3,
				title: '提示'
			}, function(index) {
				let callback = function (e) {
					layer.msg(e.msg);
					if (e.code == 0) {
						that.load(topic_id,module);
					}
				}
				gougu.delete("/api/comment/delete",{ id: id },callback);
				layer.close(index);
			});
		},
		//编辑器
		editor:function(id,topic_id,pid,padmin_id,module,txt){
			if(typeof editormd=="undefined"){
				alert('请引入editor.md.js');
				return false;
			}
			let that=this;
			layer.open({
				closeBtn: 2,
				title: false,
				type:1,
				area: ['720px', '360px'],
				content: '<div style="padding:10px 16px 0 12px;"><div id="editorBox"></div></div>',
				success: function() {
					gougu.editor('editorBox',268,txt);
				},
				btnAlign: 'c',
				btn:['确定'],
				yes: function() {
					that.add(id,topic_id,pid,padmin_id,module,layui.Editor.getHTML(),layui.Editor.getMarkdown());
				}
			})			
		}
    };
    exports('gouguComment',obj);
});  