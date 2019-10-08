<template>
	<div>
		<div v-if="!showDetails">


			<vue-good-table mode="remote"  :columns="columns" :rows="rows" :pagination-options="{ enabled: true}"
			@on-page-change="onPageChange"
			@on-sort-change="onSortChange"
			@on-column-filter="onColumnFilter"
			@on-per-page-change="onPerPageChange"
			:totalRows="totalRecords">

			<div slot="table-actions">
				<span v-if="filterPaid">
					<button @click="getPaidMeals" class="btn btn-outline-info btn-xs"><i class="fas fa-filter">&nbsp;</i>Filter Paid Meals</button>
				</span>
				<span v-if="filterNotPaid">
					<button @click="getNotPaidMeals" class="btn btn-outline-info btn-xs"><i class="fas fa-filter">&nbsp;</i>Filter Not Paid Meals</button>
				</span>
				<span v-if="filterActive">
					<button @click="getTerminatedActiveMeals" class="btn btn-outline-info btn-xs"><i class="fas fa-filter">&nbsp;</i>Filter Active & Terminated Meals</button>
				</span>
			</div>
			<template slot="table-row" slot-scope="props">
				{{props.formattedRow[props.column.field]}}

				<span v-if="props.column.field=='actions'">
					<button @click="mealDetails(props.row.id)" class="btn btn-outline-info btn-xs"><i class="fas fa-eye">&nbsp;</i>View Details</button>
				</span>
			</template>
		</vue-good-table>
	</div>
	<div v-else>

		<div class="card">
			<div class="card-header">
				<h6><strong>Details of meal nro. {{this.meal.id}}</strong></h6>
			</div>
			<div class="card-body">

				<p class="card-text"><strong>State: </strong>{{this.meal.state}}</p>
				<p class="card-text"><strong>Start Date: </strong>{{dateFormat(this.meal.start)}}</p>
				<p class="card-text"><strong>End Date: </strong>{{dateFormat(this.meal.end)}}</p>
				<p class="card-text"><strong>Waiter Responsible Nro: </strong> 

					<button @click="getWaiter" class="btn btn-link btn-xs"><i class="fas fa-eye">&nbsp;</i>View Waiter Nro. {{this.meal.responsible_waiter_id}} Details</button>
				</p>
				<p class="card-text"><strong>Total Price: </strong>{{this.meal.total_price_preview}} �</p>

				<p class="card-text"><strong>Items: </strong>
					<button @click="getItems" class="btn btn-link btn-xs"><i class="fas fa-eye">&nbsp;</i>View Meal Items</button>
				</p>

				<button @click="returnToList" class="btn btn-primary btn-xs">Return Meals List</button>
			</div>
		</div>

		<div v-if="showWaiter">
			<waiter-details :user="mealWaiter"></waiter-details>
		</div>

		<div v-if="showItems">
			<items-list :items="mealItems"></items-list>
		</div>
	</div>

</div>
</template>


<script type="text/javascript">
	/*jshint esversion: 6 */
	import itemsList from './meals/itemsMealList.vue';
	import waiterDetails from './meals/waiterMealDetails.vue';

	export default {
		data:
		function() {
			return {
				id:'1',
				meal:null,
				mealWaiter:null,
				mealItems:[],
				meals:[],
				mealsTable:[],
				mealsPaid:[],
				mealsNotPaid:[],
				mealsActiveTerminated:[],
				showDetails:false,
				filterPaid:true,
				filterNotPaid:true,
				filterActive:false,
				showWaiter:false,
				showItems:false,
				rows: [],
				totalRecords: 0,
				serverParams: {
					columnFilters: {

					},
					sort: {
						field: '',
						type: '',
					},
					page: 1,
					perPage: 10,
				},
				columns: [
				{
					label: 'Id',
					field: 'id',
				}, {
					label: 'State', 
					field: 'state',
				}, {
					label: 'Table Number', 
					field: 'table_number',
				}, {
					label: 'Start Date', 
					field: 'start',
					type: 'date',
					dateInputFormat: 'YYYY-MM-DD HH:mm:ss',
					dateOutputFormat: 'DD/MM/YYYY HH:mm:ss',
					filterOptions: {
						enabled: true,
						placeholder: 'Enter a date',
					},            
				}, {
					label: 'End Date', 
					field: 'end',
					type: 'date',
					dateInputFormat: 'YYYY-MM-DD HH:mm:ss',
					dateOutputFormat: 'DD/MM/YYYY HH:mm:ss',
					filterOptions: {
						enabled: true,
						placeholder: 'Enter a date',
					}, 

				}, {
					label: 'Responsible Waiter', 
					field: 'responsible_waiter_id',
					sortable:true,
					type:'number',
					filterOptions: {
						enabled: true,
						placeholder: 'Filter By Waiter Number',
					},
				}, {
					label: 'Total Price Preview',
					field: 'total_price_preview',
				}, {
					label:'Actions',
					field:'actions',
					sortable:false,
				}
				],
			};
		},
		methods:{
			updateParams(newProps) {
				this.serverParams = Object.assign({}, this.serverParams, newProps);
			},

			onPageChange(params) {
				this.updateParams({page: params.currentPage});
				this.loadItems();
			},

			onPerPageChange(params) {
				this.updateParams({perPage: params.currentPerPage});
				this.loadItems();
			},

			onSortChange(params) {
				this.updateParams({
					sort: {
						type: params[0].type,
						field: params[0].field,
					},
				});
				this.loadItems();
			},
			onColumnFilter(params) {
				this.updateParams(params);
				this.loadItems();
			},
			loadItems() {

				let address='';

				if(this.id=='1'){
					address='api/meals/';
				}
				else if(this.id=='2'){
					address='api/paidMeals/';
				}
				else{
					address='api/notPaidMeals';
				}


				axios.get(address+'?page='+this.serverParams.page,{
					params: {
						serverInfo:  this.serverParams
					}
				}).then(response=> {
					this.totalRecords = response.data[1];
					this.rows = response.data[0].data;
				}).catch(error=>{

				});
			},
			dateFormat(value){
				if(value==null) {
					return "No date registered";
				}
				return moment(String(value)).format('DD/MM/YYYY hh:mm:ss');
			},
			mealDetails(mealId){
				//get meal
				axios.get('api/meals/'+mealId)
				.then(response=>{
					this.meal= response.data.data;
					this.showDetails=true;
					this.showWaiter=false;
					this.showItems=false;
				}).catch(error=>{

				});
			},
			getItems(){
				//get items from meal
				axios.get('api/meals/items/'+this.meal.id)
				.then(response=>{
					this.mealItems= response.data;
					this.showItems=true;
					this.showWaiter=false;
				}).catch(error=>{

				});
			},
			getWaiter(){
				axios.get('api/user/'+this.meal.responsible_waiter_id)
				.then(response=>{
					this.mealWaiter=response.data.data;
					this.showItems=false;
					this.showWaiter=true;
				}).catch(error=>{
					return null;
				});
			},
			getPaidMeals(){
				this.id='2';
				this.loadItems();
				
				this.filterPaid=false;
				this.filterActive=true;
				this.filterNotPaid=true;
			},
			getNotPaidMeals(){
				this.id='3';
				this.loadItems();
				this.filterPaid=true;
				this.filterActive=true;
				this.filterNotPaid=false;
			},
			getTerminatedActiveMeals(){
				this.id='1';
				this.loadItems();
				this.filterPaid=true;
				this.filterActive=false;
				this.filterNotPaid=true;
			},

			returnToList(){
				this.showDetails=false;
				this.showWaiter=false;
				this.showItems=false;
			}
		},
		mounted(){
			if(this.$store.state.user==null){
				this.$router.push({ path:'/login' });
				return;
			}
			this.id='1';
		}, 
		components: {
			'items-list':itemsList,
			'waiter-details':waiterDetails,
		},
		sockets:{
			meal_created(){
				this.id='1';
				this.loadItems();
			},
			meal_terminated(){
				//console.log('allMeals create a new meal');
				this.id='1';
				this.loadItems();
			},
		},


	};

</script>
