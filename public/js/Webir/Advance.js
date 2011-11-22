//# [1] "black"   "red"     "green3"  "blue"    "cyan"    "magenta" "yellow" 
//# [8] "gray" 
//# na defaulcie jest black+red
//rozrzutu_lr=function(zx, zy, etx='domyślna etykieta osi X', ety='domyślna etykieta osi Y',
//					wygladzanie=0.5, szumx=0, szumy=0, wielkosc=6, dpi=72, nazwa='rtu_reg.png', elipsy=FALSE,
//					kolor=palette()[c(1, 2)]) {
Ext.ns('Webir.Advance');
Webir.Advance.ColorPalette = [['1','Czarny'],['2','Czerwony'],['3','Zielony'],['4','Niebieski'],['5','Błękitny'],['6','Purpurowy'],['7','Żółty'],['8','Szary']]
Webir.Advance.Options = {
	postHoc : {
		xtype : 'checkbox'
		,name : 'postHoc'
		,id : 'opt_postHoc'
		,fieldLabel : 'Analiza Post-Hoc'
		,inputValue : 'true'
		,checked : false
		,hidden : true
	}
	,etyx : {
		xtype : 'textfield'
		,name : 'etyx'
		,id : 'opt_etyx'
		,fieldLabel : 'Etykieta osi X'
		,hidden : true
	}
	,etyy : {
		xtype : 'textfield'
		,name : 'etyy'
		,id : 'opt_etyy'
		,fieldLabel : 'Etykieta osi Y'
		,hidden : true
	}
	,dpi : {
		xtype : 'combo'
		,name : 'dpi'
		,hiddenName : 'dpi'
		,id : 'opt_dpi'
		,store : [['72','Normalna (72 dpi)'],['300','Wysoka (300 dpi)']]
		,fieldLabel : 'Jakość wykresu'
		,value : '72'
		,triggerAction : 'all'
		,editable : false
		,forceSelection : true
		,hidden : true
	}
	,size : {
		xtype : 'combo'
		,id : 'opt_size'
		,name : 'size'
		,store : [['6','6 cali'],['7','7 cali'],['8','8 cali'],['9','9 cali'],['10','10 cali']]
		,fieldLabel : 'Wielkość wykresu'
		,value : '6'
		,forceSelection : true
		,editable : false
		,triggerAction : 'all'
		,hiddenName : 'size'
		,hidden : true
	}
	,jitter_x : {
		xtype : 'checkbox'
		,name : 'jitter_x'
		,fieldLabel : 'Włącz szum (oś X)'
		,checked : false
		,id : 'opt_jitter_x'
		,inputValue : '1'
		,hidden : true
	}
	,jitter_y : {
		xtype : 'checkbox'
		,name : 'jitter_y'
		,fieldLabel : 'Włącz szum (oś Y)'
		,checked : false
		,id : 'opt_jitter_y'
		,inputValue : '1'
		,hidden : true
	}
	,color_1 : {
		xtype : 'combo'
		,id : 'opt_color_1'
		,name : 'color_1'
		,hiddenName : 'color_1'
		,store : Webir.Advance.ColorPalette
		,fieldLabel : 'Kolor 1'
		,triggerAction : 'all'
		,value : '1'
		,hidden : true
		,forceSelection : true
		,editable : false
	}
	,color_2 : {
		xtype : 'combo'
		,id : 'opt_color_2'
		,name : 'color_2'
		,hiddenName : 'color_2'
		,store : Webir.Advance.ColorPalette
		,fieldLabel : 'Kolor 2'
		,value : '2'
		,hidden : true
		,triggerAction : 'all'
		,forceSelection : true
		,editable : false
	}
	,span : {
		xtype : 'combo'
		,id : 'opt_span'
		,name : 'span'
		,hiddenName : 'span'
		,store : [['0.25','0.25'],['0.5','0.50'],['0.75','0.75'],['1','1.00']]
		,fieldLabel : 'Wygładzenie'
		,triggerAction : 'all'
		,forceSelection : true
		,editable : false
		,value : '0.5'
		,hidden : true
	}
	,ellipse : {
		xtype : 'checkbox'
		,name : 'ellipse'
		,fieldLabel : 'Ellipse'
		,checked : false
		,id : 'opt_ellipse'
		,inputValue : 'true'
		,hidden : true
	}
}

Webir.Advance.Functions = {
	summary : {
		menuText : 'Podsumowanie zmiennej'
		,varMinNum : 1
		,varMaxNum : 1
		,help : 'Wybierz jedną zmienną'
	},
	chi2 : {
		menuText : 'Tabela krzyżowa'
		,varMinNum : 2
		,varMaxNum : 2
		,help : 'Wybierz dwie zmienne typu faktor'
	}
	,wilcoxon : {
		menuText : 'Test Wilcoxona'
		,varMinNum : 2
		,varMaxNum : 2
		,help : 'Wybierz jedną zmienną typu factor, następnie jedną zmienną typu integer/numeric'
	}
	,tstudent : {
		menuText : 'Test t-studenta'
		,varMinNum : 2
		,varMaxNum : 2
		,help : 'Wybierz jedną zmienną typu factor, następnie jedną zmienną typu integer/numeric'
	}
	,anova : {
		menuText : 'Analiza wariancji'
		,varMinNum : 2
		,varMaxNum : 2
		,help : 'Wybierz jedną zmienną typu factor, następnie jedną zmienną typu integer/numeric'
		,options : ['postHoc']
	}
	,kraskal : {
		menuText : 'Test Kruskala-Wallisa'
		,varMinNum : 2
		,varMaxNum : 2
		,help : 'Wybierz jedną zmienną typu factor, następnie jedną zmienną typu integer/numeric'
	}
	,correlation_Nonparam : {
		menuText : 'Korelacja nieparametryczna'
		,varMinNum : 2
		,varMaxNum : 2
		,help : 'Wybierz dwie zmienne typu integer/numeric'
	}
	,correlation_Param : {
		menuText : 'Korelacja parametryczna'
		,varMinNum : 2
		,varMaxNum : 2
		,help : 'Wybierz dwie zmienne typu integer/numeric'
	}
	,regression : {
		menuText : 'Analiza regresji'
		,varMinNum : 2
		,varMaxNum : null
		,help : 'Wybierz jedną zmienną do wyjaśnienia typu integer/numeric, następnie dowolną ilość zmiennych wyjaśniających'
	}
	,parametric : {
		menuText : 'Parametryczność'
		,varMinNum : 1
		,varMaxNum : 1
		,help : 'Wybierz jedną zmienną typu integer/numeric'
	}
	,homogeneity : {
		menuText : 'Jednorodność'
		,varMinNum : 1
		,varMaxNum : 1
		,help : 'Wybierz jedną zmienną typu factor, następnie jedną zmienną typu integer/numeric'
	}
	,chart_Box : {
		menuText : 'Wykres skrzynkowy'
		,varMin : 2
		,varMax : 2
		,help : 'Wybierz jedną zmienną typu factor, następnie jedną zmienną typu integer/numeric'
		,options : ['etyx','etyy','dpi','size']
	},
	chart_Rozrzut : {
		menuText : 'Wykres rozrzutu'
		,varMin : 2
		,varMax : 2
		,help : 'Wybierz dwie zmienne typu integer/numeric'
		,options : ['etyx','etyy','dpi','size','jitter_x','jitter_y','color_1','color_2','span','ellipse']
	}
	,chart_Bar : {
		menuText : 'Wykres słupkowy'
		,varMin : 1
		,varMax : 1
		,help : 'Wybierz jedną zmienną typu faktor'
		,options : ['etyx','etyy','dpi','size']
	},chart_BarSrednie : {
		menuText : 'Wykres słupkowy (średnie)'
		,varMin : 1
		,varMax : 1
		,help : 'Wybierz jedną zmienną typu factor, a następnie jedną zmienną typu integer/numeric'
		,options : ['etyx','etyy','dpi','size']
	},chart_Histogram : {
		menuText : 'Histogram'
		,varMin : 1
		,varMax : 1
		,help : 'Wybierz jedną zmienną typu integer/numeric'
		,options : ['etyx','etyy','dpi','size']
	}
}

Webir.Advance.Panel = Ext.extend(Ext.Panel, {
	id : 'WebirAdvancePanel'
	,height: 420
	,frame : false
	,initComponent : function() {
		this.varsNum = 2;
		
		var	source = new Webir.Variables.SourcePanel({ flex: 1, border: false, frame : false });
		var	destination = new Webir.Variables.DestinationPanel({ flex: 1, border: false, frame : false });
		var subset = new Webir.Variables.SubsetPanel({ flex: 1, border: false, frame : true });

		// Usuwamy ze stora zmienne, które są już wybrane
		source.getStore().on('load', function(store,records,options) {
			if (source.getStore().loaded != true) {
				Ext.each(Analysis.Variables, function(item,index,alItems) {
					destination.getStore().add(store.getAt(store.find('v_id',item)));
				});
			}
			
			destination.getStore().each(function(r) {
				store.removeAt(store.find('v_id',r.get('v_id')));
			});
			
			source.getStore().loaded = true;
			//analysisBeginSetDisabled(false);
		});

		// Handler dla dodawania zmiennych
		var addVariable = function() {
			
			var r = source.getSelectionModel().getSelected();
			if (!Ext.isEmpty(r)) {
				if (destination.getStore().getCount() >= Ext.getCmp('WebirAdvancePanel').varsNum || r.length > Ext.getCmp('WebirAdvancePanel').varsNum) {
					Webir.Common.msgboxInfo('Możesz wybrać najwyżej '+ Ext.getCmp('WebirAdvancePanel').varsNum +' zmienne do analizy. Jeśli chcesz wybrać inny zestaw zmiennych, musisz najpierw usunąć poprzednie.', 'Informacja');
					return false;
				}
				var addVars = [];
				var removeVars = [];
				Ext.each(r,function(item,index,allItems) {
					if(index >= (Ext.getCmp('WebirAdvancePanel').varsNum - destination.getStore().getCount())) {return false;}
					var rt = destination.getStore().recordType;
					var rd = new rt(item.data);
					addVars.push(rd);
					removeVars.push(item);
				});
				
				var values = Ext.getCmp('advanceFormCard').getForm().getValues();
				var ds = destination.getStore();
				switch(values.func) {
					case 'regression':
						if(ds.getCount() == 0) {
							source.getStore().load({
								params : {
									func : 'regression'
									,variable : 1
								}
							});
						}
					break;
					case 'homogeneity':
					case 'chart_BarSrednie':
					case 'tstudent':
					case 'wilcoxon':
					case 'kraskal':
					case 'anova':
						if(ds.getCount() == 0) {
							source.getStore().load({
								params : {
									func : values.func
									,variable : r.get('v_type')
								}
							});
						}
					break;
				}
				
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
			if(Ext.isEmpty(r)) {return false;}
			var reloaded = false;
			// obsługa różnego rodzaju analiz...
			var values = Ext.getCmp('advanceFormCard').getForm().getValues();
			var ds = destination.getStore();
			switch(values.func) {
				case 'regression':
					if(ds.getCount() > 1 && ds.indexOf(r) == 0) {
						Webir.Common.msgboxInfo('Proszę najpierw usunąć wszystkie zmienne wyjaśniające!', 'Informacja');
						return false;
					}
					
					if(ds.getCount() == 1) {
						source.getStore().load({
							params : {
								func : 'regression'
								,variable : 0
							}
						});
						reloaded = true;
					}
				break;
				case 'chart_BarSrednie':
				case 'chart_Box':
				case 'tstudent':
				case 'wilcoxon':
				case 'kraskal':
				case 'anova':
					if(ds.getCount() == 1) {
						source.getStore().load({
							params : {
								func : values.func
							}
						});
						reloaded : true;
					}
					
					if(ds.getCount() == 2) {
						source.getStore().load({
							params : {
								func : values.func
								,variable : ds.getAt(0).get('v_type')
							}
						});
						reloaded : true;
					}
				break;
			}

			destination.getStore().remove(r);
			if(reloaded == false) {
				source.getStore().reload();
			}
			
			source.getView().on('refresh',function(view) {
				view.collapseAllGroups();
				view.toggleRowIndex(source.getStore().findExact('v_segment_id',r.get('v_segment_id')) + 1,true);
			});
		}
		
		// Obsługa przycisku "Dodaj do analizy" oraz podwójnego kliknięcia wiersza
		source.on('render',function() {
			Ext.getCmp('bAddVariable').setHandler(addVariable,source);	
		});
		
		source.on('rowdblclick',function(grid,rowIndex,event) {
			addVariable();
		});
		// Obsługa przycisku "Usuń z analizy" oraz podwójnego kliknięcia wiersza
		Ext.getCmp('bRemoveVariable').setHandler(removeVariable,source);
		destination.on('rowdblclick',function(grid,rowIndex,event) {
			removeVariable();
		});
		
		// Obsługa subsetów
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
			//analysisBeginSetDisabled(true);
		});
		
		Ext.getCmp('fsSubset').on('collapse',function(fs) {
			//analysisBeginSetDisabled(true);
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
				}
				,failure : function(response,options) {
					Webir.Common.msgboxFailure("Wyłączenie zawężania obszaru analizy nie powidło się.",'Wystąpił błąd');
				}
			});
		});
		
		Ext.getCmp('cbLevelValue').on('change',function(combo,newValue,oldValue) {
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
				}
				,failure : function(response,options) {
					
					// wupluj komunikat o błędzie
				}
			});
		});
		
		var formCard = {
			id : 'advanceFormCard',
			xtype : 'form',
			height: 420,
			//forceLayout: true,
			buttonAlign: 'center',
			layout : 'hbox',
			layoutConfig: { align: 'stretch', pack: 'start' },
			url: '/ajax/advance-analysis-begin',
			method: 'POST',			
			items : [
				source,
				{
					xtype: 'panel',
					border: false,
					layout : 'vbox',
					flex: 1,
					layoutConfig: { align: 'stretch', pack: 'start' }
					,items: [ destination, subset ]
				},
				{
					xtype: 'hidden',
					name: 'func',
					value: ''
				}
			],
			buttons : [
				{
					text: 'Rozpocznij analizę',
					handler: this.addAnalysisTask
				}
			]
		};
		
		Ext.apply(this, {
			layout : 'card'
			,tbar : [{
				text : 'Wyświetl i edytuj dane'
				,id : 'editData'
				,iconCls : 'fam-pencil'
				,scope : this
				,handler : function() {
					this.getLayout().setActiveItem(0);
					Ext.get('analysisHeader').update('Analiza zaawansowana: Popraw dane');
					Ext.get('infoBar').update('Zmień nazwę, etykietę, opis lub typ zmiennej. Uporządkuj zmienną.');
				}
			},{
				text : 'Analizuj dane'
				,iconCls : 'fam-cog'
				,menu : [{
					text : Webir.Advance.Functions.summary.menuText
					,func : 'summary'
					,handler : this.analyseData
					//,scope : this
				},{
					text : Webir.Advance.Functions.chi2.menuText
					,func : 'chi2'
					,handler : this.analyseData
				},{
					text : 'Korelacja'
					,menu : [{
						text : 'Nieparametryczna'
						,func : 'correlation_Nonparam'
						,handler : this.analyseData
					},{
						text : 'Parametryczna'
						,func : 'correlation_Param'
						,handler : this.analyseData
					}]
				},{
					text : 'Porównaj średnie'
					,menu : [{
						text : Webir.Advance.Functions.tstudent.menuText
						,func : 'tstudent'
						,handler : this.analyseData
					},{
						text : Webir.Advance.Functions.wilcoxon.menuText
						,func : 'wilcoxon'
						,handler : this.analyseData
					},{
						text : Webir.Advance.Functions.anova.menuText
						,func : 'anova'
						,handler : this.analyseData
					},{
						text : Webir.Advance.Functions.kraskal.menuText
						,func : 'kraskal'
						,handler : this.analyseData
					}]
				},{
					text : Webir.Advance.Functions.regression.menuText
					,func : 'regression'
					,handler : this.analyseData
				},
				'-'
				,{
					text : Webir.Advance.Functions.parametric.menuText
					,func : 'parametric'
					,handler : this.analyseData
				},{
					text : Webir.Advance.Functions.homogeneity.menuText
					,func : 'homogeneity'
					,handler : this.analyseData
				}]
			},{
				text : 'Wizualizuj dane'
				,iconCls : 'fam-chart_curve'
				,menu : [{
					text : Webir.Advance.Functions.chart_Bar.menuText
					,func : 'chart_Bar'
					,handler : this.analyseData
				},{
					text : Webir.Advance.Functions.chart_BarSrednie.menuText
					,func : 'chart_BarSrednie'
					,handler : this.analyseData
				},{
					text : Webir.Advance.Functions.chart_Histogram.menuText
					,func : 'chart_Histogram'
					,handler : this.analyseData
				},{
					text : Webir.Advance.Functions.chart_Rozrzut.menuText
					,func : 'chart_Rozrzut'
					,handler : this.analyseData
				},{
					text : Webir.Advance.Functions.chart_Box.menuText
					,func : 'chart_Box'
					,handler : this.analyseData
				}]
			}]
			,items : [{
				xtype : 'webir-dataset-editor'
				,layout: 'fit'
				,data_set_id : Webir.Advance.Static.data_set_id
			},formCard
			]
		});

		Webir.Advance.Panel.superclass.initComponent.apply(this,arguments);
	},
	
	addAnalysisTask: function() {
		var formCard = Ext.getCmp('advanceFormCard');
		var form = formCard.getForm();
		form.submit({
			scope : this
      ,success: function(form, action) {
        var obj = Ext.decode(action.response.responseText);
        Ext.Msg.show({
        	buttons : {
        		yes : 'Rozpocznij nową analizę'
        		,no : 'Przejdź do listy analiz'
        	}
        	,fn : function(buttonId, text, opt) {
        		var destination = formCard.items.get(1).items.get(0);
        		destination.clearAllVariables();
        		
        		var source = formCard.items.get(0);
        		var lastOptions = source.getStore().lastOptions || {};
        		var params = lastOptions.params || {};
        		params.variable = null;
        		
        		Ext.apply(lastOptions, {
        			params : params
        		});
        		source.getStore().load(lastOptions);
        		if(buttonId != 'yes') {
        			window.location = '/analysis'
        		}
        	}
        	,scope : this
        	,closable : false
        	,modal : true
        	,title : 'Nowe zadanie'
        	,msg : 'Zadanie zostało dodane do kolejki.'
        	,icon : Ext.MessageBox.INFO
        });
	    //	Webir.Common.msgboxInfo('Poprawnie dodano nowe zadanie do analizy. Aby zobaczyć postęp analizy, przejdź na podstronę "Twoje analizy"', 'Tworzenie nowego zadania');
      },
			// Dostaliśmy kod 4xx lub 5xx            
      failure: function(form, action) {
      	var obj = Ext.decode(action.response.responseText);
      	Webir.Common.msgboxFailure(obj.error, 'Tworzenie nowego zadania');
      }
		}
		);
		
	},
	
	analyseData: function(menuItem, event) {
		var panel = Ext.getCmp('WebirAdvancePanel');
		panel.getLayout().setActiveItem(1);
		
		var formCard = Ext.getCmp('advanceFormCard');
		var source = formCard.items.get(0);
		var destination = formCard.items.get(1).items.get(0);
		var subset = formCard.items.get(1).items.get(1);
		var func = Webir.Advance.Functions[menuItem.func];
		var advanced = subset.findById('AdvancedOptions');
		
		// jeśli funkcja nie posiada opcji, to chowam panel opcji dodatkowych
		if(Ext.isEmpty(func.options)) {
			advanced.hide();
		} else {
			advanced.show();
			// chowam wszystkie opcje...
			advanced.items.each(function(item) {
				item.hide();
			});
			// pokazuje potrzebne
			Ext.each(func.options,function(item,index,allItems) {
				Ext.getCmp('opt_' + item).show();
			});
		}

		Ext.getCmp('WebirAdvancePanel').varsNum = menuItem.varsNum;
		// Dobieramy parametry w zależności od typu testu
		source.getStore().load({
			params : {func : menuItem.func }
		});
		
		Ext.get('analysisHeader').update('Analiza zaawansowana: ' + func.menuText);
		Ext.get('infoBar').update(func.help || 'Ten element nie ma pomocy');
		
		// Usuwamy dotychczas wybrane zmienne
		destination.clearAllVariables();		
		// Show variable chooser
		formCard.getForm().setValues({func : menuItem.func })
	}	
});
Webir.Advance.OrderGrid = Ext.extend(Ext.grid.GridPanel, {
	column_id : 0
	,ordered : false
	,width : 450
	,height : 200
	,initComponent : function() {
		Ext.apply(this, {
			loadMask : true
			,store : {
				xtype : 'jsonstore'
				,root : 'data'
				,url : '/ajax/get-levels-r'
				,method : 'GET'
				,baseParams : {
					column_id : this.column_id
				}
				,fields : [{name : 'value'}]
			}
			,sm : new Ext.grid.RowSelectionModel({singleSelect : true})
			,enableDragDrop : true
			,columns : [{
				header : 'Poziom'
				,dataIndex : 'value'
				,sortable : false
			}]
			,plugins: [new Ext.ux.dd.GridDragDropRowOrder({
        scrollable: true
       })]
			,view : new Ext.grid.GridView({
				forceFit : true
			})
			,bbar : [{
				xtype : 'checkbox'
				,checked : this.ordered
			},'Ustaw typ <i>ordered factor</i>','->',{
				text : 'Zatwierdź'
				,iconCls : 'fam-tick'
				,scope : this
				,handler : function() {
					var data = [];
					this.getStore().each(function(item) {
						data.push(item.get('value'));
					});
					Ext.Ajax.request({
						url : '/ajax/advance-levels-order'
						,scope : this
						,params : {
							column_id : this.column_id
							,levels : Ext.encode(data)
							,ordered : this.getBottomToolbar().items.get(0).getValue()
						}
						,success : function(res,options) {
							var response = Ext.decode(res.responseText);
							if(response.success == false) {
								Webir.Common.msgboxFailure(response.error,'Wystąpił błąd');
								return false;
							}
							this.ownerCt.close();
							var store = Ext.getCmp('DataSetEditPanel').getStore(); 
							var r = store.getAt(store.find('id',this.column_id));
							r.beginEdit();
							r.set('is_ordered',response.data.ordered);
							r.commit();
						}
					})
				}
			}]
		});
		
		Webir.Advance.OrderGrid.superclass.initComponent.apply(this,arguments);
	}
	,onRender : function() {
		this.getStore().load();
		Webir.Advance.OrderGrid.superclass.onRender.apply(this,arguments);
	}
});

Webir.Advance.NaForm = Ext.extend(Ext.form.FormPanel, {
	column_id : 0
	,width : 330
	,initComponent : function() {
		Ext.apply(this, {
			frame : true
			,labelAlign : 'right'
			,labelWidth : 60
			,errorReader : new Ext.data.JsonReader({
				root : 'error'
				,fields : [{
					name : 'id'
				},{
					msg : 'msg'
				}]
			})
			,items : {
				xtype : 'combo'
				,fieldLabel : 'Poziom'
				,width : 250 
				,store : new Ext.data.JsonStore({
					xtype : 'jsonstore'
					,url : '/ajax/get-levels-r'
					,root : 'data'
					,baseParams : {
						column_id : this.column_id
					}
					,fields : [{name : 'value'}]
					,msgTarget : 'under'
				})
				,name : 'level'
				,hiddenName : 'level'
				,valueField : 'value'
				,displayField : 'value'
				,triggerAction : 'all'
				,editable : false
			}
			,buttons : [{
				text : 'Zatwierdź'
				,iconCls : 'fam-tick'
				,scope : this
				,handler : function() {
						this.getForm().submit({
							url : '/ajax/advance-change-na'
							,params : {
								column_id : this.column_id
							}
							,scope : this
							,failure : function(form,action) {
								var obj = Ext.decode(action.response.responseText);
      					Webir.Common.msgboxFailure(obj.error, 'Tworzenie nowego zadania');
							}
							,success: function() {
								this.ownerCt.close();
							}
						});
				}
			}]
		});
		
		Webir.Advance.NaForm.superclass.initComponent.apply(this,arguments);
	}
});

Ext.onReady(function() {
	var advance = new Webir.Advance.Panel();
	advance.render('advancePanel')
	advance.getLayout().setActiveItem(0);
});