Ext.ns('Webir.DataSet');
Ext.ns('Webir.DataSet.List');

/**
 * Panel edycji zbioru danych
 * @class Webir.DataSet.EditPanel
 * @extends Ext.grid.EditorGridPanel
 */
Webir.DataSet.EditPanel = Ext.extend(Ext.grid.EditorGridPanel,{
	data_set_id : 0
	,id : 'DataSetEditPanel'
	,initComponent : function() {
		Ext.apply(this, {
			store : {
				xtype : 'jsonstore'
				,proxy : new Ext.data.HttpProxy({
					api : {
						read : {url : '/ajax/getcolumns',method : 'GET'}
						,update : {url : '/ajax/savecolumn',method : 'POST'}
					}
				})
				,writer : new Ext.data.JsonWriter({
					writeAllFields : false
					,listful : true
				})
				,sortInfo : {
					field : 'c_index'
					,direction : 'ASC'
				}
				,remoteSort : true
				,root : 'data.rows'
				,totalProperty : 'data.total'
				,idProperty : 'c_id'
				,fields : [{
					name : 'c_id'
					,type : 'integer'
				},{
					name : 'c_index'
				},{
					name : 'c_type'
				},{
					name : 'c_label'
				},{
					name : 'c_label_short'
				},{
					name : 'c_description'
				},{
					name : 'c_is_ordered'
					,type : 'boolean'
				}]
			}
			,sm : new Ext.grid.RowSelectionModel({
				singleSelect : true
			})
			,bbar : ['->',{
				text : 'Zamień na braki danych'
				,iconCls : 'fugue-sort-arrow'
				,scope : this
				,disabled : true
				,handler : function() {
					var r = this.getSelectionModel().getSelected();
					var window = new Ext.Window({
						modal : true
						,resizable : false
						,items : new Webir.Advance.NaForm({
							column_id : r.get('c_id')
						})
						,title : 'Wybierz poziom do zamiany'
					});
					
					window.show();
					
					window.items.get(0).getForm().on('actioncomplete',function() {
						window.close();
					});
				}
			},{
				text : 'Uporządkuj poziomy'
				,iconCls : 'fugue-sort-number'
				,scope : this
				,disabled : true
				,handler : function() {
					var r = this.getSelectionModel().getSelected();
					var window = new Ext.Window({
						modal : true
						,resizable : false
						,items : new Webir.Advance.OrderGrid({
							column_id : r.get('c_id')
							,ordered : r.get('c_is_ordered')
						})
						,title : 'Uporządkuj poziomy'
					});
					
					window.show();
//					window.items.get(0).getForm().on('actioncomplete',function() {
//						window.close();
//					});
				}
			}]
			,columns : [{
				dataIndex : 'c_index'
				,header : 'Zmienna'
				,sortable : true
			},{
				dataIndex : 'c_is_ordered'
				,header : 'Ordered'
				,width : 50
				,renderer : function(val, meta,r) {
					if(r.get('type') == 'factor' && val) {
						return '<span class="icon-label fam-accept">&#160;</span>';
					} else {
						return '';
					}
				}
			},{
				dataIndex : 'c_type'
				,header : 'Typ zmiennej'
				,editor : {
					xtype : 'combo'
					,store : [['integer','integer'],['numeric','numeric'],['factor','factor'],['logical','logical']]
					,triggerAction : 'all'
					,editable : false
				}
				,renderer : Webir.Common.ComboRenderer
				,sortable : true
			},{
				dataIndex : 'c_label'
				,header : 'Etykieta pełna'
				,editor : {xtype : 'textfield'}
				,width : 250
				,sortable : true
			},{
				dataIndex : 'c_label_short'
				,header : 'Etykieta krótka'
				,editor : {xtype : 'textfield'}
				,width : 100
				,sortable : true
			},{
				dataIndex : 'c_description'
				,id : 'cDescription'
				,header : 'Opis'
				,editor : {xtype : 'textfield'}
				,width : 200
				,sortable : true
			}]
			,view : new Ext.grid.GridView({
				forceFit : true
			})
			,autoExpandColumn : 'cDescription'
		});
		
		Webir.DataSet.EditPanel.superclass.initComponent.apply(this,arguments);
		
		this.getSelectionModel().on('selectionchange',function(sm) {
			var r = sm.getSelected();
			if(!Ext.isEmpty(r)) {
				this.getBottomToolbar().items.get(1).enable();
				r.get('c_type') == 'factor' ? this.getBottomToolbar().items.get(2).enable() : this.getBottomToolbar().items.get(2).disable();
			} else {
				this.getBottomToolbar().items.get(1).disable();
			}
		},this);
	}
	,onRender : function() {
		Webir.DataSet.EditPanel.superclass.onRender.apply(this,arguments);
		this.getStore().load({params : {
			data_set_id : this.data_set_id
		}});
	}
});

Ext.reg('webir-dataset-editor',Webir.DataSet.EditPanel);

Webir.DataSet.List.Simple = Ext.extend(Ext.grid.GridPanel, {
	width : 'auto'
	,height : 146
	,id : 'DastaSetGridSimple'
	,initComponent : function() {
		
		this.store = new Ext.data.JsonStore({
			fields : [{
				name : 'name'
			},{
				name : 'id'
				,type : 'integer'
			},{
				name : 'created_at'
				,type : 'date'
				,dateFormat : 'Y-m-d H:i:s'
			},{
				name : 'source_filename'
			}]
			,root : 'data.rows'
			,totalProperty : 'data.total'
			,proxy : new Ext.data.HttpProxy({
				api : {
					read : '/ajax/getdatasets'
				}
			})
			,sortInfo : {
				field : 'created_at'
				,direction : 'DESC'
			}
			,baseParams : {
				mode : 'simple'
			}
			,idProperty : 'id'
			,remoteSort : true
		});
		
		Ext.apply(this, {
			sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
			border: true,
			columns : [{
				dataIndex : 'id'
				,header : 'ID'
				,hidden : true
				,width : 50
			},{
				dataIndex : 'name'
				,header : 'Zestaw danych'
				,sortable : true
				,width : 200
				,id : 'colName'
			},{
				dataIndex : 'created_at'
				,header : 'Utworzono'
				,renderer : Ext.util.Format.dateRenderer('Y-m-d H:i:s')
				,sortable : true
				,width : 100
				,resizable : false
			}]
			,autoExpandColumn : 'colName'
			,frame: true
			,view : new Ext.grid.GridView({
				forceFit : true
			})
		});
		
		Webir.DataSet.List.Simple.superclass.initComponent.apply(this, arguments);		
	}
	,onRender : function() {
		Webir.DataSet.List.Simple.superclass.onRender.apply(this, arguments);
		this.getStore().load();
	}
});

Webir.DataSet.List.Normal = Ext.extend(Ext.grid.GridPanel, {
	width : 'auto'
	,title : 'Lista zestawów danych'
	,iconCls : 'fam-database'
	,height : 300
	,id : 'DataSetListNormal'
	,initComponent : function() {
		Ext.apply(this, {
			store : {
				xtype : 'jsonstore'
				,fields : [{
					name : 'name'
				},{
					name : 'format'
				},{
					name : 'id'
					,type : 'integer'
				},{
					name : 'status_id'
					,type : 'integer'
				},{
					name : 'created_at'
					,type : 'date'
					,dateFormat : 'Y-m-d H:i:s'
				},{
					name : 'source_filename'
				}]
				,root : 'data.rows'
				,totalProperty : 'data.total'
				,proxy : new Ext.data.HttpProxy({
					api : {
						read : '/ajax/getdatasets'
						,destroy : '/ajax/deletedataset'
					}
				})
				,sortInfo : {
					field : 'created_at'
					,direction : 'DESC'
				}
				,baseParams : {
					mode : 'normal'
				}
				,idProperty : 'id'
				,remoteSort : true
				,writer : new Ext.data.JsonWriter({
					listful : true
				})
			},
			sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
			border: true,
			columns : [{
				dataIndex : 'id'
				,header : 'ID'
				,hidden : true
				,width : 50
			},{
				dataIndex : 'format'
				,header : 'Format'
				,width : 50
			},{
				dataIndex : 'name'
				,header : 'Nazwa zestawu danych'
				,sortable : true
				,width : 200
				,id : 'colName'
			},{
				dataIndex : 'source_filename'
				,header : 'Plik źródłowy'
				,width : 150
			},{
				dataIndex : 'created_at'
				,header : 'Utworzono'
				,renderer : Ext.util.Format.dateRenderer('Y-m-d H:i:s')
				,sortable : true
				,width : 100
				,resizable : false
			},{
				dataIndex : 'status_id'
				,id: 'status'
				,header : 'Status'
				,width : 100
				,renderer : function(val) {
					if(val > 0 && val <=4) {
						var status = Webir.DataSet.Status[val-1];
						switch(val) {
							case 1: return '<span class="icon-label fam-hourglass">'+status+'</span>';break;
							case 2: return '<span class="icon-label fam-cog">'+status+'</span>';break;
							case 3: return '<span class="icon-label fam-accept">'+status+'</span>';break;
							case 4: return '<span class="icon-label fam-delete">'+status+'</span>';break;
						}
					} else {
						return val;
					}
				}
					
			}]
			,autoExpandColumn : 'colName'
			,frame: true
			,view : new Ext.grid.GridView({
				forceFit : true
			})
			,bbar : ['->',/*{
				text : 'Edytuj zestaw danych'
				,iconCls : 'fam-pencil'
				,scope : this
				,handler : function() {
					var r = this.getSelectionModel().getSelected();
					if(Ext.isEmpty(r)) {
						return false;
					}
					
					editDataSet(r.get('id'));
				}
			},*/{
				text : 'Usuń zestaw danych'
				,scope : this
				,iconCls : 'fam-delete'
				,handler : function() {
					var r = this.getSelectionModel().getSelected();
					if(Ext.isEmpty(r)) {
						return false;
					}
					
					Ext.Msg.confirm('Usuwanie zestawu danych','Czy na pewno chcesz usunąć wybrany zestaw danych?',function(buttonId,text,opt){
						if(buttonId == 'yes') {
							this.getStore().remove(r);
						} else {
							return false;
						}
					},this);
				}
			}]
		});
		
		var editDataSet = function(id) {
			window.location = '/dataset/edit/' + id;
		}
		
		Webir.DataSet.List.Normal.superclass.initComponent.apply(this, arguments);		
	}
	,onRender : function() {
		Webir.DataSet.List.Normal.superclass.onRender.apply(this, arguments);
		this.getStore().load();
	}
});

Webir.DataSet.UploadForm = Ext.extend(Ext.form.FormPanel,{
	border : false
	,initComponent : function() {
		Ext.apply(this,{
			fileUpload: true
			,errorReader : new Ext.data.JsonReader({
				root : 'errors'
				,fields : [{
					name : 'id'
				},{
					msg : 'msg'
				}]
			})
			,items : [{
				xtype : 'fieldset'
				,title : 'Nowy zestaw danych'
				,checkboxToggle : true
				,layout : 'column'
				,collapsed : true
				,items : [{
					layout : 'form'
					,border : false
					,columnWidth : 0.4
					,labelAlign : 'right'
					,labelWidth : 110
					,defaults : {
						anchor : '100%'
					}
					,items : [{
						xtype : 'textfield'
						,fieldLabel : 'Nazwa'
						,name : 'name'
						,msgTarget : 'under'
					},{
						xtype : 'combo'
						,mode : 'local'
						,store : {
							xtype : 'jsonstore'
							,data : Webir.DataSet.AvailableEncoding
							,fields : [{
								name : 'value'
							},{
								name : 'label'
							}]
						}
						,fieldLabel : 'Kodowanie znaków'
						,value : Webir.DataSet.DefaultEncoding
						,name : 'fileEncoding'
						,displayField : 'label'
						,valueField : 'value'
						,hiddenName : 'fileEncoding'
						,triggerAction : 'all'
						,editable : false
						,forceSelection : true
						,msgTarget : 'under'
					},{
						xtype : 'fileuploadfield'
						,fieldLabel : 'Plik'
						,name : 'dataset'
						,buttonText : 'Przeglądaj...'
						,msgTarget : 'under'
					},{
						xtype : 'hidden'
						,name : 'format'
						,value : 'csv'
					},{
						xtype : 'fieldset'
						,title : 'Zaawansowane'
						,checkboxToggle : true
						,collapsed : true
						,layout : 'form'
						,labelAlign : 'right'
						,labelWidth : 100
						,defaults : {
							anchor : '100%'
						}
						,items : [{
							xtype : 'combo'
							,mode : 'local'
							,store : {
								xtype : 'jsonstore'
								,data : [{value : ';',label : 'Średnik'},{value : ",",label : 'Przecinek'},{value : '\t',label : 'Tabulator'}]
								,fields : [{
									name : 'value'
								},{
									name : 'label'
								}]
							}
							,fieldLabel : 'Separator pola'
							,value : ";"
							,name : 'sep'
							,displayField : 'label'
							,valueField : 'value'
							,hiddenName : 'sep'
							,triggerAction : 'all'
							,editable : false
							,forceSelection : true
							,msgTarget : 'under'
						},{
							xtype : 'combo'
							,mode : 'local'
							,store : {
								xtype : 'jsonstore'
								,data : [{value : '\\\"',label : 'Cudzysłów (")'},{value : "'",label : 'Apostrof (\')'}]
								,fields : [{
									name : 'value'
								},{
									name : 'label'
								}]
							}
							,fieldLabel : 'Separator tekstu'
							,value : "\\\""
							,name : 'quote'
							,displayField : 'label'
							,valueField : 'value'
							,hiddenName : 'quote'
							,triggerAction : 'all'
							,editable : false
							,forceSelection : true
							,msgTarget : 'under'
						},{
							xtype : 'combo'
							,mode : 'local'
							,store : {
								xtype : 'jsonstore'
								,data : [{value : 'NA',label : 'NA'},{value : " ",label : 'Spacja'},{value : "",label : "Pusty znak"}]
								,fields : [{
									name : 'value'
								},{
									name : 'label'
								}]
							}
							,fieldLabel : 'Brak danych'
							,value : " "
							,name : 'na_strings'
							,displayField : 'label'
							,valueField : 'value'
							,hiddenName : 'na_strings'
							,triggerAction : 'all'
							,editable : false
							,forceSelection : true
							,msgTarget : 'under'
						},{
							xtype : 'combo'
							,mode : 'local'
							,store : {
								xtype : 'jsonstore'
								,data : [{value : ',',label : 'Przecinek'},{value : ".",label : 'Kropka'}]
								,fields : [{
									name : 'value'
								},{
									name : 'label'
								}]
							}
							,fieldLabel : 'Sep. dziesiętny'
							,value : ","
							,name : 'dec'
							,displayField : 'label'
							,valueField : 'value'
							,hiddenName : 'dec'
							,triggerAction : 'all'
							,editable : false
							,forceSelection : true
							,msgTarget : 'under'
						}]
					},{
						xtype : 'button'
						,text : 'Wyślij'
						,name : 'submit'
						,scope : this
						,handler : function() {
		        	this.getForm().submit({
		        		method : 'POST'
		        		,url: '/dataset/new',
		        		waitMsg: 'Wgrywanie zbioru danych',
		        		scope : this
		        		,params : { asXmlHttpRequest : true }
		        		,success: function(fp, action){
		        			Ext.getCmp('DataSetListNormal').getStore().reload();
		        			this.getForm().reset();
		        			this.items.get(0).collapse(false);
		        		}
								,failure : function(fp, action){
									var obj = Ext.decode(action.response.responseText);
									if(!Ext.isEmpty(obj.error)) {
      							Webir.Common.msgboxFailure(obj.error, 'Nowy zestaw danych');
									}
								}
		       	 });
						}
					}]
				},{
					xtype : 'panel'
					,border : false
					,bodyStyle : 'margin-left: 0.5em;'
					,html : '<p class="info" style="margin-top: 0">Wpisz w polu <strong>Nazwa</strong> czytelną dla ciebie nazwę zestawu danych. Na przykład: "Badanie popularności produktu". Zadbaj aby w pierwszym wierszu znalazły się etykiety kolumn. Jeżeli plik CSV, który wgrywasz, posługuje się innym kodowaniem niż Windows-1250, wybierz je z listy. Jeśli nie wiesz, jakie jest kodowanie Twojego pliku, nie zmieniaj tego pola. Następnie wybierz plik CSV z dysku i użyj przycisku <strong>Wyślij</strong>.</p>'
					,columnWidth : 0.6
				}]
			}]
		});
		
		Webir.DataSet.UploadForm.superclass.initComponent.apply(this,arguments);
	}
});

Ext.onReady(function() {
	var container;
	
	// Main DataSet Grid
	container = Ext.fly('edit-grid');
	if (container !== null) {
		try {
			var grid = new Webir.DataSet.EditPanel({ data_set_id : Ext.get('edit-grid').dom.firstElementChild.title });
			grid.render(container);
		} catch (exception) {
			Webir.Common.exceptionMessage(exception);
		}
	}
	
	container = Ext.fly('DataNormalGrid');
	if(!Ext.isEmpty(container)) {
		var grid = new Webir.DataSet.List.Normal();
		grid.render(container);
		Ext.TaskMgr.start({
		run : function() {
			if(grid.getStore().findExact('status_id',1) > -1 || grid.getStore().findExact('status_id',2) > -1) {
				grid.getStore().reload();
			}
		},interval : 5000
	});
	}
	
	container = Ext.fly('DataUploadForm');
	if(!Ext.isEmpty(container)) {
		var form = new Webir.DataSet.UploadForm();
		form.render(container);
	}
	
	// Simple Grid
	container = Ext.fly('DataSimpleGrid');
	if (container !== null) {
		try {
			var grid = new Webir.DataSet.List.Simple();
			var form = new Ext.FormPanel({
				standardSubmit : true
				,url : '/analysis/advance'
				,frame: true,
				title : 'Twoje zestawy danych',
				iconCls: 'fam-database',
        width: 'auto',
        layout: 'fit',
        items: [ {
        	xtype : 'hidden'
        	,name : 'data_set_id'
        },grid ],
        buttonAlign: 'left',
        buttons: [{
        	text : 'Tryb podstawowy'
        	,iconCls: 'fam-bullet_go'
        	,disabled: true
        },{
        	text : 'Tryb zaawansowany'
        	,iconCls: 'fam-bullet_go'
        	,disabled: true
        },'->',{
          text: 'Dodaj nowy',
          iconCls: 'fam-add'
          // scale: 'large'
        }],
        renderTo: container
			});
			
			var handlerNew = function() {
				window.location = "/dataset"
			};
			
			var handlerContinueBasic = function() {
				var r = grid.getSelectionModel().getSelected();
				if(Ext.isEmpty(r)) {
					return false;
				}
				window.location = "/analysis/choose-variables/" + r.get('id');
			};
			
			var handlerContinueAdvance = function() {
				var r = grid.getSelectionModel().getSelected();
				if(Ext.isEmpty(r)) {
					return false;
				}
				
				this.items.get(0).setValue(r.get('id'));
				
				this.getForm().submit();
			};
			
			
			
			// Enable 'Use selected set' button if there's a selected row; if not -- disable 
			grid.getSelectionModel().on('selectionchange', function(sm) {
				form.buttons[0].setDisabled(sm.getCount() == 0);
				form.buttons[1].setDisabled(sm.getCount() == 0);
			});
			// Set appropriate button handlers
			form.buttons[0].setHandler(handlerContinueBasic, form);
			form.buttons[1].setHandler(handlerContinueAdvance, form);
			form.buttons[3].setHandler(handlerNew, form);
		} catch (exception) {
			Webir.Common.exceptionMessage(exception);
		}
	}

});