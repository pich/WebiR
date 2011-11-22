Ext.ns('Webir.Task');

Webir.Task.Grid = Ext.extend(Ext.grid.GridPanel, {
	width : 'auto'
	,title : 'Twoje analizy danych'
	,frame : true
	,iconCls: 'fam-chart_pie'
	,height : 300
	,adminMode : false
	,initComponent : function() {
		this.taskStore = new Ext.data.JsonStore({
			fields : [{
				name : 't_name'
			},{
				name : 't_id'
				,type : 'integer'
			},{
				name : 't_created_at'
				,type : 'date'
				,dateFormat : 'Y-m-d H:i:s'
			},{
				name : 't_seen_at'
				,type : 'date'
				,dateFormat : 'Y-m-d H:i:s'
			},{
				name : 't_status_id'
				,type :'integer'
			},{
				name : 'u_email'
			}]
			,root : 'data.rows'
			,totalProperty : 'data.total'
			,autoSave : true
			,proxy : new Ext.data.HttpProxy({
				api : {
					read : '/ajax/gettasks'
					,update : '/ajax/updatetask'
					,destroy : '/ajax/deletetask'
				}
			})
			,writer : new Ext.data.JsonWriter({
    		encode: true
    		,writeAllFields: false // write all fields, not just those that changed
    		,listful : true
			})
			,sortInfo : {
				field : 't_created_at'
				,direction : 'DESC'
			}
			,idProperty : 't_id'
			,remoteSort : true
		});
		
		Ext.apply(this,{
			store : this.taskStore
			,columns : [{
				dataIndex : 't_id'
				,header : 'ID'
				,hidden : true
				,width : 50
			},{
				dataIndex : 't_name'
				,header : 'Nazwa'
				,sortable : true
				,width : 200
				,id : 'colName'
			},{
				dataIndex : 't_created_at'
				,header : 'Utworzono'
				,renderer : Ext.util.Format.dateRenderer('Y-m-d H:i:s')
				,sortable : true
				,width : 100
				,resizable : false
			},{
				dataIndex : 't_status_id'
				,id: 'status'
				,header : 'Status'
				,renderer : function(val) {
					switch(val) {
						case 1: return '<span class="icon-label fam-hourglass">W kolejce</span>';break;
						case 2: return '<span class="icon-label fam-cog">Trwająca</span>';break;
						case 3: return '<span class="icon-label fam-tick">Zakończona sukcesem</span>';break;
						case 4: return '<span class="icon-label fam-cancel">Anulowana</span>';break;
						case 5: return '<span class="icon-label fam-cross">Zakończona błędem</span>';break;
					}
				}
				,sortable : true
				,width : 100
				,resizable : false
			},{
				dataIndex : 'u_email'
				,header : 'Właściciel'
				,width : 200
				,hidden : !Webir.User.admin_mode
				,sortable : true
			}]
			,autoExpandColumn : 'colName'
			,view : new Ext.grid.GridView({
				forceFit : true
				,getRowClass : function(record,index,rowParams,store) {
					if(Ext.isEmpty(record.get('t_seen_at')) && (record.get('t_status_id') == 3 || record.get('t_status_id') == 5)) {
						return 'unseen';
					}
					return '';
				}
			})
			,sm: new Ext.grid.RowSelectionModel({singleSelect:true})
			,bbar : new Ext.PagingToolbar({
				store : this.taskStore
				,pageSize : 25
				,items : ['->',{
					text : Webir.User.admin_mode ? 'Tryb użytkownika' : 'Tryb administratora'
					,id : 'btnAdminMode'
					,iconCls : 'fam-arrow_refresh'
					,scope : this
					,hidden : !Webir.User.is_admin
					,handler : function() {
						this.toggleAdmin();
					}
				},'-',{
					text : 'Zobacz wynik analizy'
					,iconCls: 'fam-eye'
					,disabled : true
					,name : 'bShowResult'
					,scope : this
					,handler : function() {
						showResult(this.getSelectionModel().getSelected());
					}
				},{
					text : 'Zmień nazwę'
					,iconCls: 'fam-chart_pie_edit'
					,disabled : true
					,name : 'bRename'
					,scope : this
					,handler : function() {
						Ext.Msg.prompt('Zmiana nazwy analizy', 'Wprowadź proszę, nową nazwę dla analizy:', function(btn, text){
						    if (btn == 'ok') {
						    	var id = this.getSelectionModel().getSelected().id;
						    	var record = this.getStore().getById(id);
						    	record.set('t_name', text);
						    }
						}, this);
					}
				},{
					text : 'Anuluj analizę'
					,iconCls: 'fam-cancel'
					,disabled : true
					,name : 'bCancel'
					,scope : this
					,handler : function() {
						this.getSelectionModel().getSelected().set('t_status_id',4); 
					}
				},{
					text : 'Usuń analizę'
					,iconCls: 'fam-delete'
					,disabled : true
					,name : 'bDelete'
					,scope : this
					,handler : function() {
						Ext.Msg.confirm('Usuwanie analizy','Czy na pewno chcesz usunąć analizę oraz jej wyniki?',function(buttonId,text,opt) {
							if(buttonId == 'yes') {
								this.getStore().remove(this.getSelectionModel().getSelected());
							}
						},this);
					}
				}]
			})
		});
		
		Webir.Task.Grid.superclass.initComponent.apply(this,arguments);
		
		var showResult = function(r) {
			if(Ext.isEmpty(r) || r.get('status_id' != 3)) {
				return false;
			}
			
			window.location = "/analysis/result/" + r.get('t_id');
		}
		
		this.getSelectionModel().on('selectionchange',function(sm) {

			var bResult = this.getBottomToolbar().find('name', 'bShowResult');
			var bCancel = this.getBottomToolbar().find('name', 'bCancel');
			var bDelete = this.getBottomToolbar().find('name', 'bDelete');
			var bRename = this.getBottomToolbar().find('name', 'bRename');
			
			if (Ext.isEmpty(sm.getSelected())) {
				bCancel[0].disable();
				bDelete[0].disable();
				bResult[0].disable();
				bRename[0].disable();
			} else {
				if(sm.getSelected().get('t_status_id') == 1 || sm.getSelected().get('t_status_id') == 2) {
					bCancel[0].enable();
				}
				
				if(sm.getSelected().get('t_status_id') != 2) {
					bDelete[0].enable();
				}
				
				if(sm.getSelected().get('t_status_id') == 3 || sm.getSelected().get('t_status_id') == 5) {
					bResult[0].enable();
				}
				bRename[0].enable();
			}
		}, this);
		
		this.on('rowdblclick',function(grid,rowIndex,event) {
			showResult(grid.getStore().getAt(rowIndex));
		});
	}
	,onRender : function() {
		Webir.Task.Grid.superclass.onRender.apply(this,arguments);
		var lastOptions = this.getStore().lastOptions || {};
		var params = lastOptions.params || {};
		params.adminMode = this.adminMode;
		Ext.apply(lastOptions,{
			params : params
		});
		
		this.getStore().baseParams.adminMode = this.adminMode;
		this.getBottomToolbar().changePage(1);
		delete(this.getStore().baseParams.adminMode);
	}
	,toggleAdmin : function() {
		this.adminMode = !this.adminMode;
		Ext.getCmp('btnAdminMode').setText((this.adminMode ? 'Tryb użytkownika' : 'Tryb administratora'));
		this.getStore().baseParams.adminMode = this.adminMode ? 'true' : 'false';
		
		// pokazuje/chowam nazwę użytkownika
		this.getColumnModel().setHidden(4,!this.adminMode);
		this.getStore().reload();
	}
});

Ext.onReady(function(){
	var g = new Webir.Task.Grid({adminMode : Webir.User.admin_mode});
	g.render('task-grid');
	Ext.TaskMgr.start({
		run : function() {
			if(g.getStore().findExact('t_status_id',1) > -1 || g.getStore().findExact('t_status_id',2) > -1) {
				g.getStore().reload();
			}
		},interval : 5000
	});
});