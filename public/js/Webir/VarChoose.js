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
				},{
					name : 'v_description'
				}, {
					name : 'v_position'
					,type : 'integer'
				}, {
					name : 's_name'
				},{
					name : 'v_segment_id'
					,type : 'integer'
				} , {
					name : 's_position'
					,type : 'integer'
				} ]
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
			height : 280,
			width : 460,
			iconCls: "fam-table_go",
			frame: true,
			title : 'Dostępne zmienne'
			,
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
					,toolTip : 'Zwiń wszystkie segmenty'
					,handler : function() {
						this.getView().collapseAllGroups();
					}
					,scope : this 
				},{
					iconCls : 'ico-expand_all'
					,toolTip : 'Rozwiń wszystkie segmenty'
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
			if (Webir.User.accessibility === false) {
				if(Ext.isEmpty(this.getStore().baseParams.query)) {
					view.collapseAllGroups();
				} else {
					view.expandAllGroups();
				}
			} else {
				view.expandAllGroups();
				this.getSelectionModel().selectRow(0);
				this.fireEvent('rowclick', this, 0);				
			}
		},this);
		
		this.getSelectionModel().on('selectionchange',function(sm) {
			var r = sm.getSelected();
			if(Ext.isEmpty(r)) {
				$('#variableInfo').hide();
			} else {
				if(Ext.isEmpty(r.get('s_name'))) {
					$('#variableSegment').parent().hide();	
				} else {
					$('#variableSegment').text(r.get('s_name'));
					$('#variableSegment').parent().show();
				}
				
				if(Ext.isEmpty(r.get('v_label'))) {
					$('#variableLabel').parent().hide();	
				} else {
					$('#variableLabel').text(r.get('v_label'));
					$('#variableLabel').parent().show();
				}
				
				if(Ext.isEmpty(r.get('v_description'))) {
					$('#variableDescription').parent().hide();	
				} else {
					$('#variableDescription').text(r.get('v_description'));
					$('#variableDescription').parent().show();
				}
				$('#variableInfo').show();
			}
		});
	}
	,onRender : function() {
		Webir.Variables.SourcePanel.superclass.onRender.apply(this, arguments);
		this.getStore().load();
	},
	afterRender : function() {
		Webir.Variables.SourcePanel.superclass.afterRender.call(this);
    this.keyNav = new Ext.KeyNav(this.getEl(),{
    	'enter': function(e) {
    		this.fireEvent('rowdblclick');
    	},
    	scope: this
    });
	}
}); // Webir.Variables.SourcePanel

Webir.Variables.DestinationPanel = Ext.extend(Ext.grid.GridPanel, {
	initComponent : function() {
		Ext.apply(this, {
			id : 'DestinationGridPanel',
			title : 'Analizowane zmienne',
			frame: true,
			iconCls: "fam-table_gear",
			height : 140
			,width : 460
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
					} ,{
						name : 'v_segment_id'
						,type : 'integer'
					}, {
						name : 's_name'
					} , {
						name : 's_position'
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
				sortable : true
			},{
				header : 'Typ'
				,dataIndex : 'v_type'
				,sortable : true
				,width : 12
			} ],
			view : new Ext.grid.GridView( {
				forceFit : true
			}),
			bbar : [ '->',{
				text : 'Usuń z analizy'
				,id : 'bRemoveVariable'
				,toolTip : 'Usuń zmienną z analizy'
				,iconCls: 'fam-delete',
				scope : this
				,disabled : false
			} ]
			
		});
		Webir.Variables.DestinationPanel.superclass.initComponent.apply(this, arguments);
	},
	afterRender : function() {
		Webir.Variables.DestinationPanel.superclass.afterRender.call(this);
    this.keyNav = new Ext.KeyNav(this.getEl(),{
    	'enter': function(e) {
    		this.fireEvent('rowdblclick');
    	},
    	scope: this
    });
	}

}); // Webir.Variables.DestinationPanel


Ext.onReady(function() {
var vars = Analysis.Variables;
	

	
	var subsetVar = Ext.isEmpty(Analysis.Subsets[0]) ?  [] : [Analysis.Subsets[0].variable];
	var subsetVal = Ext.isEmpty(Analysis.Subsets[0]) ? [] : [Analysis.Subsets[0].value];
	
	var subsetForm = new Ext.form.FormPanel({
		border : false
		,labelAlign : 'top'
		,width : 460
		,items : [{
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
	
	var source = new Webir.Variables.SourcePanel();
	var destination = new Webir.Variables.DestinationPanel();
	
	// Handler dla dodawania zmiennych
	var addVariable = function() {
		
		var r = source.getSelectionModel().getSelections();
		if (!Ext.isEmpty(r)) {
			if (destination.getStore().getCount() >= 2 || r.length > 2) {
				Webir.Common.msgboxInfo('Możesz wybrać najwyżej dwie zmienne do analizy. Jeśli chcesz wybrać inny zestaw zmiennych, musisz najpierw usunąć poprzednie.', 'Informacja');
				return false;
			}
			var addVars = [];
			var removeVars = [];
			Ext.each(r,function(item,index,allItems) {
				if(index >= (2 - destination.getStore().getCount())) {return false;}
				var rt = destination.getStore().recordType;
				var rd = new rt(item.data);
				addVars.push(rd);
				removeVars.push(item);
			});
			destination.getStore().add(addVars);
			source.getStore().remove(removeVars);
		} else {
			Webir.Common.msgboxInfo('Proszę najpierw zaznaczyć co najmniej jedną zmienną na liście.', 'Informacja');
			return false;
		}
	}
	
	// Handler dla usuwania zmiennych
	var removeVariable = function() {
		var r = destination.getSelectionModel().getSelected();
		destination.getStore().remove(r);
		source.getStore().reload();
		
		source.getView().on('refresh',function(view) {
			//view.collapseAllGroups();
			view.toggleRowIndex(source.getStore().findExact('v_segment_id',r.get('v_segment_id')) + 1,true);
		});
	}
	
	source.render('SourceGrid');
	destination.render('DestinationGrid');
	subsetForm.render('SubsetForm');
	
	// obsługa przycisku "Dodaj do analizy" oraz podwójnego kliknięcia wiersza
	Ext.getCmp('bAddVariable').setHandler(addVariable,source);
	source.on('rowdblclick',function(grid,rowIndex,event) {
		addVariable();
	});
	
	// disablowanie przycisku "Dodaj do analizy"
	source.getSelectionModel().on('selectionchange',function(sm) {
//		if(sm.getSelections().length > 0 && destination.getStore().getCount() < 2) {
//			Ext.getCmp('bAddVariable').enable();
//		} else {
//			Ext.getCmp('bAddVariable').disable();
//		}
	});
	
	source.getStore().on('add',function(store,records,index) {
//		if(destination.getStore().getCount() < 2 && source.getSelectionModel().getSelections().length > 0) {
//			Ext.getCmp('bAddVariable').enable();
//		} else {
//			Ext.getCmp('bAddVariable').disable();
//		}
	});
	
	// disablowanie przycisku "Usuń z analizy"
	destination.getSelectionModel().on('selectionchange',function(sm) {
//		if(sm.getSelections().length > 0) {
//			Ext.getCmp('bRemoveVariable').enable();			
//		} else {
//			Ext.getCmp('bRemoveVariable').disable();
//		}
	});
	
	// obsługa przycisku "Usuń z analizy" oraz podwójnego kliknięcia wiersza
	Ext.getCmp('bRemoveVariable').setHandler(removeVariable,source);
	destination.on('rowdblclick',function(grid,rowIndex,event) {
		removeVariable();
	});
	
	// usuwam ze store'a zmienne, które są już wybrane
	source.getStore().on('load',function(store,records,options) {
		analysisBeginSetDisabled(false);
	});
	
	Ext.getCmp('cbLevelVariable').on('select',function(field,newVal,oldVal) {
		if(Ext.isEmpty(newVal)) {
			Ext.getCmp('cbLevelValue').disable(); 
		} else {
			Ext.getCmp('cbLevelValue').clearValue();
			Ext.getCmp('cbLevelValue').enable();
		}
	});
	
	Ext.getCmp('cbLevelValue').on('beforequery',function(queryEvent) {
		queryEvent.combo.lastQuery = "#lastQuery#"; // wymuszam odświeżanie store'a
		queryEvent.combo.getStore().baseParams.column_id = Ext.getCmp('cbLevelVariable').getValue(); 
	});
	
	Ext.getCmp('fsSubset').on('expand',function(fs) {
		analysisBeginSetDisabled(true);
	});
	
	Ext.getCmp('fsSubset').on('collapse',function(fs) {
		analysisBeginSetDisabled(true);
		Ext.Ajax.request({
			method : 'POST'
			,url : '/ajax/removesubset'
			,params : {
				level_id : Ext.getCmp('cbLevelValue').getValue()
			}
			,success : function(response,options) {
				var r = Ext.decode(response.responseText);
				if(r.success == false) {
					Webir.Common.msgboxFailure(r.error,'Wystąpił błąd');
					return false;
				}
				Ext.getCmp('cbLevelValue').clearValue();
				Ext.getCmp('cbLevelValue').disable();
				Ext.getCmp('cbLevelVariable').clearValue();
				analysisBeginSetDisabled(false);
			}
			,failure : function() {
				// wypluj komunikat o błędzie
			}
		});
	});
	
	
	
	Ext.getCmp('cbLevelValue').on('change',function(combo,newValue,oldValue) {
		analysisBeginSetDisabled(true);
		Ext.Ajax.request({
			method : 'POST'
			,url : '/ajax/setsubset'
			,params : {
				from : oldValue
				,to : newValue
			}
			,success : function(response,options) {
				var r = Ext.decode(response.responseText);
				if(r.success == false) {
					Webir.Common.msgboxFailure(r.error,'Wystąpił błąd');
					return false;
				}
				analysisBeginSetDisabled(false);
			}
			,failure : function() {
				// wupluj komunikat o błędzie
			}
		});
	});
	
	destination.getStore().on('beforesave',function() {
		analysisBeginSetDisabled(true);
	});
	
	destination.getStore().on('save',function() {
		analysisBeginSetDisabled(false);
	});
	
	var analysisBeginSetDisabled = function(val) {
		if(val == false) {
			if(destination.getStore().getCount() == 0 || (!Ext.getCmp('fsSubset').collapsed && Ext.isEmpty(Ext.getCmp('cbLevelValue').getValue()))) {
				Ext.get('analysisBegin').set({disabled : 'disabled'});				
			} else {
				Ext.get('analysisBegin').set({disabled : ''},false);
			}
		} else {
			Ext.get('analysisBegin').set({disabled : 'disabled'});
		}
	}
	
	analysisBeginSetDisabled(false);
});
