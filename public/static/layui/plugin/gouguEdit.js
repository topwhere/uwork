layui.define(['employeepicker'], function (exports) {
	const layer = layui.layer;
	const gougu = layui.gougu;
	const table = layui.table;
	const laydate = layui.laydate;
	const dropdown = layui.dropdown;
	const employeepicker = layui.employeepicker;
	const obj = {
		//文本
		text: function (id, name, real_txt, editPost) {
			let that = this;
			layer.open({
				type: 1,
				title: '请输入内容',
				area: ['500px', '158px'],
				content: '<div style="padding:5px;"><input class="layui-input" id="goguEditInput" value="' + real_txt + '"/></div>',
				btnAlign: 'c',
				btn: ['提交保存'],
				yes: function () {
					let newval = $("#goguEditInput").val();
					if (newval != '') {
						editPost(id, name, newval, newval);
					} else {
						layer.msg('请输入内容');
					}
				}
			})
		},
		//文本
		textarea: function (id, name, real_txt, editPost) {
			let that = this;
			layer.open({
				type: 1,
				title: '请输入内容',
				area: ['800px', '360px'],
				content: '<div style="padding:5px;"><textarea class="layui-textarea" id="goguEditTextarea" style="width: 100%; height: 240px;">' + real_txt + '</textarea></div>',
				btnAlign: 'c',
				btn: ['提交保存'],
				yes: function () {
					let newval = $("#goguEditTextarea").val();
					if (newval != '') {
						editPost(id, name, newval, newval);
					} else {
						layer.msg('请输入内容');
					}
				}
			})
		},
		//员工单选
		employee_one: function (id, name, show_txt, real_txt, editPost) {
			let that = this;
			employeepicker.init({
				ids: real_txt.toString(),
				names: show_txt,
				department_url: "/api/index/get_department_tree",
				employee_url: "/api/index/get_employee",
				type: 0,
				callback: function (ids, names) {
					editPost(id, name, names, ids);
				}
			});
		},
		//员工多选
		employee_more: function (id, name, show_txt, real_txt, editPost) {
			let that = this;
			let ids = [];
			let names = [];
			if (real_txt != '') {
				ids = real_txt.toString().split(',');
				names = show_txt.split(',');
			}
			employeepicker.init({
				ids: ids,
				names: names,
				department_url: "/api/index/get_department_tree",
				employee_url: "/api/index/get_employee",
				type: 1,
				callback: function (ids, names) {
					editPost(id, name, names.join(','), ids.join(','));
				}
			});
		},
		//表格单选
		select_type: function (id, name, real_val, data, editPost) {
			let that = this;
			let i = data.length;
			while (i--) {
				if (data[i].id == real_val) {
					data.splice(i, 1);
				}
			}
			if (data.length == 0) {
				layer.msg('无可选择的内容');
				return false;
			}
			layer.open({
				title: '请选择',
				type: 1,
				area: ['500px', '360px'],
				content: '<div style="padding:16px 16px 0"><div id="selectBox"></div></div>',
				success: function () {
					selectable = table.render({
						elem: '#selectBox',
						cols: [
							[{
								type: 'radio',
								title: '选择',
								width: 80
							}, {
								field: 'title',
								title: '选项'
							}]
						],
						data: data
					});
				},
				btn: ['确定'],
				btnAlign: 'c',
				yes: function () {
					var checkStatus = table.checkStatus(selectable.config.id);
					var data = checkStatus.data;
					if (data.length > 0) {
						editPost(id, name, data[0].title, data[0].id);
					}
					else {
						layer.msg('请选择');
					}
				}
			})
		},
		//下拉选择
		dropdown: function (id, name, real_val, data, editPost, is_cancel) {
			let that = this;
			let i = data.length;
			while (i--) {
				if (data[i].id == real_val) {
					data.splice(i, 1);
				}
			}
			if (data.length == 0) {
				layer.msg('无可关联的内容');
				return false;
			}
			if (is_cancel) {
				data.push({ id: 0, title: '<span style="color:#FF5722">取消关联</span>' });
			}
			dropdown.render({
				elem: '#' + name + '_' + id
				, show: true
				, data: data
				, click: function (data, othis) {
					editPost(id, name, data.title, data.id);
				}
			});
		},
		//日期
		date: function (id, name, real_txt, editPost) {
			let that = this;
			laydate.render({
				elem: '#' + name + '_' + id
				, showBottom: false
				, show: true //直接显示
				, value: real_txt
				, done: function (value, date) {
					editPost(id, name, value, value);
				}
			});
		},
		//编辑器
		editor: function (id, name, real_txt, editPost) {
			if (typeof editormd == "undefined") {
				alert('请引入editor.md.js');
				return false;
			}
			let that = this;
			layer.open({
				closeBtn: 2,
				title: false,
				type: 1,
				area: ['1080px', '580px'],
				content: '<div style="padding-right:3px"><div id="editorBox" style="margin:0 auto!important;"></div></div>',
				success: function () {
					gougu.editor('editorBox', real_txt, 520, true);
				},
				btnAlign: 'c',
				btn: ['提交保存'],
				yes: function () {
					editPost(id, name, layui.Editor.getHTML(), layui.Editor.getMarkdown());
				}
			})
		}
	};
	exports('gouguEdit', obj);
});  