Ext.ns('Devel');

Devel.IconSearch = Ext.extend(Ext.grid.GridPanel, {
	initComponent : function() {
		this.store = {
			xtype : 'jsonstore'
			,url : '/devel/iconsearchajax'
			,method : 'GET'
			,fields : [{
				name : 'iconCls'
			}]
			,baseParams : {
				limit : 25
				,start : 0
			}
			,successProperty : 'success'
			,root : 'data.rows'
			,totalProperty : 'data.total'
		};
		
		Ext.apply(this,{
			title : 'IconSearch'
			,iconCls : 'fam-zoom'
			,store : this.store
			,columns : [{
				header : 'Ikona'
				,dataIndex : 'iconCls'
				,renderer : function(value, metaData, record) {
					return '<span class="icon-label ' + value + '">' + value + '</span>';
				}
			}]
			,view : new Ext.grid.GridView({
				forceFit : true
			})
			,sm : new Ext.grid.RowSelectionModel({
				singleSelect : true
			})
			,tbar : ['Zestaw ikon: ',{
				xtype : 'combo'
				,store : {
					xtype : 'jsonstore'
					,data : [{value : 'fam'},{name : 'Fugue'}]
					,fields : [{name : 'name'}]
				}
				,value : 'fam'
				,valueField : 'value'
				,displayField : 'value'
				,triggerAction : 'all'
			}]
			,plugins : [new Ext.ux.grid.Search({
				store : this.store
				,iconCls:'fam-zoom'
				,width : 'auto'
				,readonlyIndexes:['iconCls']
				,minChars:3
				,autoFocus:true
			})]
			,bbar : []
		});
		
		Devel.IconSearch.superclass.initComponent.apply(this,arguments);
	}
	,onRender : function() {
		Devel.IconSearch.superclass.onRender.apply(this,arguments);
	}
}); 

Ext.onReady(function() {
	var grid = new Devel.IconSearch({width : 400,height : 300});
	grid.render('iconSearch');
});