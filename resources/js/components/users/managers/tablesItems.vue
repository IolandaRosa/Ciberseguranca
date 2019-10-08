<template>
	<div>
		<div v-if="showTables">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-4">

						<div class="text-center">
							<tables-list :tables="tables" @table-create="createTable" @table-edit="editTable"@table-delete="deleteTable"></tables-list>
						</div>

					</div>
					<div class="col">
						<div class="text-center">
							<items-list :items="items" @item-create="createItem" @item-edit="editItem" @item-delete="deleteItem"></items-list>
						</div>

					</div>
				</div>
			</div>
		</div>

		<table-create-edit v-if="showCreateEditTable" :isEdit="isEdit" :table="tableNumber" @cancel-table-click="cancelTable" @table-changed-click="tableChanged"></table-create-edit>

		<item-create-edit v-if="showCreateEditItem" :isEdit="isEdit" :item="item" @cancel-click="cancelTable" @items-changed-click="itemsChanged"></item-create-edit>

	</div>
</template>

<script type="text/javascript">

	/*jshint esversion: 6 */

	import tablesList from './tables/tablesList.vue';
	import tablesCreateEdit from './tables/tablesCreateEdit.vue';
	import managersItemsList from './items/managersItemsList.vue';
	import itemsCreateEdit from './items/itemsCreateEdit.vue';

	export default {
		data:
		function() {
			return {
				tables:[],
				items:[],
				showTables:true,
				showCreateEditTable:false,
				showCreateEditItem:false,
				isEdit:false,
				tableNumber:'',
				item:{},
			};
		},
		methods:{
			getAllTables(){
				axios.get('api/tables')
				.then(response=>{
					this.tables = response.data.data;
				}).catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}

				});
			},
			getAllItems(){
				axios.get('api/items')
				.then(response=>{
					this.items = response.data.data;
				}).catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}

				});
			},
			createTable(){
				this.showTables=false;
				this.showCreateEditTable=true;
				this.isEdit=false;
			},
			cancelTable(){
				this.showTables=true;
				this.showCreateEditTable=false;
				this.showshowCreateEditItem=false;
			},
			tableChanged(){
				this.showTables=true;
				this.showCreateEditTable=false;
				this.showCreateEditItem=false;
				this.getAllTables();
			},
			editTable(tableNumber){
				this.showTables=false;
				this.showCreateEditTable=true;
				this.isEdit=true;
				this.tableNumber=tableNumber;
			},
			deleteTable(tableNumber){
				axios.delete('api/tables/'+tableNumber).
				then(response=>{
					this.getAllTables();
					this.$socket.emit('inform-managers-waiters-new-table', this.$store.state.user,this.tableNumber);
				}).
				catch(error=>{

				});
			},
			createItem(){
				this.showTables=false;
				this.showCreateEditItem=true;
				this.showCreateEditTable=false;
				this.isEdit=false;
			},
			itemsChanged(){
				this.showTables=true;
				this.showCreateEditTable=false;
				this.showCreateEditItem=false;
				this.getAllItems();
			},
			editItem(id){

				axios.get('api/items/'+id)
				.then(response=>{
					this.item=response.data.data;
					this.showTables=false;
					this.showCreateEditItem=true;
					this.showCreateEditTable=false;
					this.isEdit=true;
				})
				.catch(error=>{
					console.log(error.data);
				});
			},
			deleteItem(id){
				axios.delete('api/items/'+id).
				then(response=>{
					this.getAllItems();
					this.$socket.emit('inform-item-alteration', this.$store.state.user,id);
				}).
				catch(error=>{

				});
			}
		},
		mounted(){
			if(this.$store.state.user==null){
				this.$router.push({ path:'/login' });
				return;
			}

			this.getAllTables();
			this.getAllItems();
		}, 
		components: {
			'tables-list':tablesList,
			'items-list':managersItemsList,
			'table-create-edit':tablesCreateEdit,
			'item-create-edit':itemsCreateEdit,
		},
		sockets:{
			refresh_get_table_numbers(){
				this.getAllTables();
			},

			refresh_items(){
				this.getAllItems();
			}
		},
	};
</script>