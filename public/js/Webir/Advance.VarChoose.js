Ext.layout.FormLayout.prototype.trackLabels = true;

Ext.ns('Webir.Variables');

Webir.Variables.SourcePanel = Ext.extend(Ext.grid.GridPanel, {
	initComponent : function() {
		this.store = {
			xtype : 'groupingstore',
			proxy : new Ext.data.HttpProxy({
				url : '/ajax/getvariables'
				,method : 'GET'
			})
			,baseParams : {
				data_set_id : Analysis.data_set_id
			}
			,reader : new Ext.data.JsonReader( {
				root : 'data.rows',
				fields : [ {
					name : 'v_id',
					type : 'integer'
				}, {
					name : 'v_label'
				}, {
					name : 'v_type'
				}, {
					name : 'v_position'
					,type : 'integer'
				}, {
					name : 's_name'
				} , {
					name : 's_position'
					,type : 'integer'
				}, {
					name : 'v_segment_id'
					,type : 'integer'
				}  ]
				,totalProperty : 'data.total'
				,successProperty : 'success'
				,idProperty : 'v_id'
			})
			,sortInfo : {
				field : 'v_position',
				direction : 'ASC'
			}
			,groupField : 's_position'
			,remoteSort : true
			,remoteGroup : true
		}
		Ext.apply(this, {
			id : 'SourceGridPanel',
			iconCls: "fam-table_go",
			title : 'Dostępne zmienne'
			,store : this.store,
			columns : [ {
				header : 'Nazwa zmiennej',
				dataIndex : 'v_label',
				sortable : true
			}, {
				header : 'Typ'
				,dataIndex : 'v_type'
				,sortable : true
				,width : 12
			}, {
				header : 'Segment'
				,dataIndex : 's_position'
				,renderer : function(value, metaData, record, rowIndex, colIndex, store) {
					var r = store.getAt(rowIndex);
					return r.get('s_name');
				}
			} ]
			,plugins : [new Ext.ux.grid.Search({
				iconCls:'fam-zoom'
				,width : 200
				,readonlyIndexes:['v_label']
				,disableIndexes:['v_id','v_position','s_position','v_type','v_segment_id']
				,minChars:3
				,autoFocus:false
				,itemsPrepend : false
				,items : ['->', {
					text : 'Dodaj do analizy'
					,id : 'bAddVariable'
					,toolTip : 'Dodaj zmienną do analizy'
					,iconCls: 'fam-add',
					scope : this
				},'-',{
					iconCls : 'ico-collapse_all'
					,toolTip : 'Zwiń wszystki segmenty'
					,handler : function() {
						this.getView().collapseAllGroups();
					}
					,scope : this 
				},{
					iconCls : 'ico-expand_all'
					,toolTip : 'Rozwiń wszystki segmenty'
					,handler : function() {
						this.getView().expandAllGroups();
					}
					,scope : this
				} ]
			})]
			,sm : new Ext.grid.RowSelectionModel({singleSelect : true})
			,view : new Ext.grid.GroupingView( {
				forceFit : true
				,groupTextTpl: '{text} ({[values.rs.length]})'
				,enableNoGroups : false
				,enableGroupingMenu : false
				,hideGroupedColumn : true
				,startCollapsed : true
				,deferEmptyText : 'Poczekaj na załadowanie danych...'
			}),
			bbar : []
		}); // Ext.apply()

		Webir.Variables.SourcePanel.superclass.initComponent.apply(this, arguments);
		
		this.getView().on('refresh',function(view){
			if(Ext.isEmpty(this.getStore().baseParams.query)) {
				view.collapseAllGroups();
			} else {
				view.expandAllGroups();
			}
		},this);
	}
	,onRender : function() {
		Webir.Variables.SourcePanel.superclass.onRender.apply(this, arguments);
		this.getStore().load();
	}
}); // Webir.Variables.SourcePanel

Webir.Variables.DestinationPanel = Ext.extend(Ext.grid.GridPanel, {
	clearAllVariables: function() {
		this.store.removeAll();
	},
	initComponent : function() {
		Ext.apply(this, {
			id : 'DestinationGridPanel',
			title : 'Analizowane zmienne',
			iconCls: "fam-table_gear",
			height : 118
			,store : {
				xtype : 'jsonstore',
				root : 'data.rows'
				,totalProperty : 'data.total'
				,proxy : new Ext.data.HttpProxy({
					api : {
						create : '/ajax/addvariable'
						,destroy : '/ajax/removevariable'
					}
				})
				,idProperty : 'v_id'
				,fields : [ {
						name : 'v_id',
						type : 'integer'
					}, {
						name : 'v_label'
					}, {
						name : 'v_type'
					}, {
						name : 'v_position'
						,type : 'integer'
					}, {
						name : 's_name'
					} , {
						name : 's_position'
						,type : 'integer'
					},{
						name : 'v_segment_id'
						,type : 'integer'
					} ]
				,writer : new Ext.data.JsonWriter({
					listful : true
					,writeAllFields : false
				})
			},
			columns : [ {
				header : 'Nazwa zmiennej',
				dataIndex : 'v_label',
				sortable : false
			},{
				header : 'Typ'
				,dataIndex : 'v_type'
				,sortable : false
				,width : 12
			} ],
			view : new Ext.grid.GridView( {
				forceFit : true
			}),
			sm : new Ext.grid.RowSelectionModel({singleSelect : true})
			,bbar : [ {
				text : 'Usuń z analizy'
				,id : 'bRemoveVariable'
				,toolTip : 'Usuń zmienną z analizy'
				,iconCls: 'fam-delete',
				scope : this
				,disabled : false
			} ]
			
		});
		Webir.Variables.DestinationPanel.superclass.initComponent.apply(this, arguments);
	}
}); // Webir.Variables.DestinationPanel
	
Webir.Variables.SubsetPanel = Ext.extend(Ext.Panel, {
	initComponent: function() {
		var subsetVar = Ext.isEmpty(Analysis.Subsets[0]) ?  [] : [Analysis.Subsets[0].variable];
		var subsetVal = Ext.isEmpty(Analysis.Subsets[0]) ? [] : [Analysis.Subsets[0].value];
		
		var options = []
		for(option in Webir.Advance.Options) {
			options.push(Webir.Advance.Options[option]);
		}
		
		Ext.apply(this, {
			border : false
			,bodyStyle: 'padding: 4px'
			,layout : 'form'
			//,labelAlign : 'top'
			,autoScroll : true
			,defaults : {
				width : 430
			}
			,items : [
				{
					xtype : 'fieldset'
					,forceLayout: true
					,collapsed : true
					,collapsible : true
					,title : 'Ustawienia zaawansowane'
					,id : 'AdvancedOptions'
					,hidden : false
					,labelWidth : 110
					,labelAlign : 'right'
					,defaults : {anchor : '100%'}
					,items : options
				},
				{
				xtype : 'fieldset'
				,id : 'fsSubset'
				,checkboxToggle : true
				,collapsed : Ext.isEmpty(Analysis.Subsets[0])
				,title : 'Zawężanie obszaru analizy'
				,items : [{
				xtype : 'combo'
				,mode : 'remote'
				,anchor : '100%'
				,id : 'cbLevelVariable'
				,store : {
					xtype : 'groupingstore'
					,proxy : new Ext.data.HttpProxy({
						url : '/ajax/getcolumns'
						,method : 'GET'
					})
					,data : {
						data : {
							rows : subsetVar
						}
					}
					,baseParams : {
						type : 'factor'
						,subset: 'true'
						,data_set_id : Analysis.data_set_id
					}
					,sortInfo : {
						field : 'c_label'
						,direction : 'ASC'
					}
					,groupField : 'c_segment_id'
					,remoteGroup : true
					,remoteSort : true
					,reader : new Ext.data.JsonReader({
						root : 'data.rows'
						,idProperty : 'id'
						,totalProperty : 'data.total'
						,fields : [{
							name : 'c_id'
							,type : 'integer'
						},{
							name : 'c_label'
						},{
							name : 's_name'
						}]
					})
				}
				,tpl: new Ext.XTemplate(
					'<tpl for=".">',
					'<tpl if="this.s_name != values.s_name">',
					'<tpl exec="this.s_name = values.s_name"></tpl>',
					'<h4>{s_name}</h4>',
					'</tpl>',
					'<div class="x-combo-list-item">{c_label}</div>',
					'</tpl>'
				)
				,pageSize : 25
				,triggerAction : 'all'
				,forceSelection : true
				,forceAll : true
				,displayField : 'c_label'
				,valueField : 'c_id'
				,fieldLabel : 'Zmienna'
				,typeAhead : true
				,value : Ext.isEmpty(subsetVar[0]) ? null : subsetVar[0].id
			},{
					xtype : 'combo'
					,mode : 'remote'
					,anchor : '100%'
					,fieldLabel : 'Wartość'
					,id : 'cbLevelValue'
					,method : 'GET'
					,store : {
						xtype : 'jsonstore'
						,proxy : new Ext.data.HttpProxy({
							url : '/ajax/getlevels'
							,method : 'GET'
						})
						,root : 'data.rows'
						,data : { 
							data : {
								rows : subsetVal 
							}
						}
						,idProperty : 'id'
						,totalProperty : 'data.total'
						,sortInfo : {
							field : 'position'
							,direction : 'ASC'
						}
						,remoteSort : true
						,fields : [{
							name : 'id'
							,type : 'integer'
						},{
							name : 'value'
						}]
					}
					,triggerAction : 'all'
					,disabled : Ext.isEmpty(Analysis.Subsets[0])
					,valueField : 'id'
					,displayField : 'value'
					,value : Ext.isEmpty(subsetVal[0]) ? null : subsetVal[0].id
				}]
			}]
		});		
		
		Webir.Variables.SubsetPanel.superclass.initComponent.apply(this, arguments);
		
	}

})
