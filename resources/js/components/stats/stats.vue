<template>
	<div>
		<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

		<select-user :users="cooks" :message="'Please select a Cook'" @orders-stats="getStatsOrderByDay"></select-user>

		<select-user :users="waiters" :message="'Please select a Waiter'" @orders-stats="getStatsOrderByDay"></select-user>


		<button class="btn btn-outline-success btn-xs mb-2" @click="getStatsMealByDay"><i class="fas fa-search"></i>View Average Meals Handled by Day By Waiters</button>
		<button class="btn btn-outline-info btn-xs mb-2" @click="getAvgOrdersCook"><i class="fas fa-search"></i>View Average Orders Handled by Day By Cooks</button>
		<button class="btn btn-outline-primary btn-xs mb-2" @click="getAvgOrdersWaiters"><i class="fas fa-search"></i>View Average Orders Handled by Day By Waiters</button>

		<hr class="separator">
		
		<h6 align="center"><strong>Performance of Restaurant By Month</strong></h6>
		<div class="form-row">
			<div class="col">
				<button class="btn btn-outline-primary btn-xs mb-2" @click="getTotalOrdersAndMealsByMonth"><i class="fas fa-search"></i>Total Orders And Meals Handled By Month</button>
			</div>
			<div class="col">
				<button class="btn btn-outline-info btn-xs mb-2" @click="getAvgTimeByMealByMonth"><i class="fas fa-search"></i>Average Minutes to Handle a Meal by Month</button>
			</div>
			<div class="col">
			</div>
		</div>
		
		
		<div class="form-row">
			<div class="col" align="right">
				<strong>Please select a month: &nbsp;</strong>
			</div>
			<div class="col">
				
				<select v-model="month" id="selectMonth" name="selectMonth" class="form-control mb-2 mr-sm-2">
					<option v-for="m in months"> {{m.date}} </option>
				</select>
			</div>
			<div class="col">
				<button class="btn btn-outline-success btn-xs mb-2" @click="getAvgTimeByOrderItemsByMonth"><i class="fas fa-search"></i>Average Minutes to Handle a Items on Order by Month</button>
			</div>
		</div>
		
		


		<div v-if="showChart">
			<chart-orders-day :l="dates" :d="values" :s="mess" :xString="xx" :yString="yy"></chart-orders-day>
		</div>

		
		<div v-if="showChartMonth">
			<chart-month-rest :l="dates" :d1="values" :s1="mess" :d2="d2" :s2="s2" :xString="xx" :yString="yy"></chart-month-rest>
		</div>

	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */
	import selectUser from './selectUser.vue';
	import chartOrders from './chartsOrderByDay.vue';
	import chartMonth from './chartsByMonth.vue';
	import showMessage from '../helpers/showMessage.vue';

	export default {
		data: 
		function() {
			return {
				cooks:[],
				waiters:[],
				showChart:false,
				showChartMonth:false,
				dates:[],
				values:[],
				averageMeals:[],
				mess:'',
				xx:'',
				yy:'',
				d2:'',
				s2:'',
				month:'',
				months:[],
				showMessage:false,
				typeofmsg: "",
				message:'',
			};
		},
		methods: {
			getMonths(){
				axios.get('api/statistics/ordersMonths').then(response=>{
					this.months=response.data[0];
				}).catch(error=>{

				});
			},
			getCooks(){
				axios.get('api/users/cooks/').then(response=>{
					this.cooks = response.data.data;
				}).catch(error=>{

				});
			},
			getWaiters(){
				axios.get('api/users/waiters/').then(response=>{
					this.waiters = response.data.data;
				}).catch(error=>{

				});
			},
			getStatsOrderByDay(user){
				this.showChart=false;
				this.showChartMonth=false;
				this.values=[];
				this.dates=[];
				axios.get('api/statistics/orders/'+user).then(response=>{

					let orders=response.data.data;

					let totals=[];
					let total=0;

					//igualar a data á primeira data que vem no vetor
					let date=moment(orders[0].start).format('YYYY-MM-DD');

					//guardar as datas
					let index=0;
					this.dates[0]=date;

					for (let i = 0; i < orders.length; i++) {
						if(date==moment(orders[i].start).format('YYYY-MM-DD')){

							total++;
							if(i==orders.length-1){
								totals[index]=total;
								this.dates[index]=date;
							}
						}
						else{
							
							totals[index]=total;
							this.dates[index]=date;
							index++;
							total=1;
							
							//atualiza a data com a data da posição
							date=moment(orders[i].start).format('YYYY-MM-DD');
							

							if(this.date!=moment(orders[i+1].start).format('YYYY-MM-DD')){
								totals[index]=total;
								this.dates[index]=date;
								index++;
								total=0;
								date=moment(orders[i+1].start).format('YYYY-MM-DD');
							}
						}

					}

					//Colocar valores unicos para cada data (no máximo há dois repetidos)
					let newDates=[];
					let newValues=[];

					for (let i = 0; i < totals.length; i++) {

						if(i!=totals.length-1){
							if(this.dates[i]==this.dates[i+1]){
								newDates.push(this.dates[i+1]);
								newValues.push(totals[i]+totals[i+1]);
								i++;
							}else{
								newDates.push(this.dates[i]);
								newValues.push(totals[i]);
							}
						}else{
							newDates.push(this.dates[i]);
							newValues.push(totals[i]);
						}
					}

					totals=newValues;
					this.dates=newDates;

					let handled=0;
					let handledTotal=[];
					let value=totals[0];
					index=0;

					for (var i = 0; i < orders.length; i++) {
						if(i<value){
							if(orders[i].state=='delivered' || orders[i].state=='not delivered'){
								handled++;
							}
						}
						else{
							
							if(i>0 && totals[index]>1){
								handledTotal.push(handled);
							}

							if(i>0 && totals[index+1]==1){
								handled=0;
								if(orders[i].state=='delivered' || orders[i].state=='not delivered'){
									handled++;
								}
								handledTotal.push(handled);
							}

							handled=0;

							if(totals[index+1]>1){
								if(orders[i].state=='delivered' || orders[i].state=='not delivered'){
									handled++;
								}
							}
							
							index++;
							value+=totals[index];
							
						}
					}


					for (var i = 0; i < totals.length; i++) {
						let res=0;
						if(handledTotal[i]>0)
							res=(handledTotal[i]/totals[i])*100;
						this.values.push(res);
						
					}

					this.mess='% of Orders Handled By Day';
					this.xx='Date';
					this.yy='% Orders Handled';
					this.showChart=true;

				}).catch(error=>{

				});
			},
			getStatsMealByDay(user){
				this.showChart=false;
				this.showChartMonth=false;
				axios.get('api/statistics/meals/').then(response=>{

					let totals=[];
					let users=[];

					for (var i = 0; i < response.data[1].length; i++) {
						users.push(response.data[1][i].resp);
						totals.push(response.data[1][i].total/response.data[0]);
					}

					this.dates=users;
					this.values=totals;
					this.mess='Average of Meals Handled By Day By User';
					this.xx='Waiter Id';
					this.yy='Average Meals';
					this.showChart=true;

				}).catch(error=>{

				});
			},
			getAvgOrdersCook(){
				this.showChart=false;
				this.showChartMonth=false;
				axios.get('api/statistics/ordersCooks').then(response=>{

					let totals=[];
					let users=[];

					for (var i = 0; i < response.data[1].length; i++) {
						users.push(response.data[1][i].resp);
						totals.push(response.data[1][i].total/response.data[0]);
					}

					this.dates=users;
					this.values=totals;
					this.mess='Average of Meals Handled By Day By Cook';
					this.xx='Cook Id';
					this.yy='Average Meals';
					this.showChart=true;

				}).catch(error=>{

				});

			},
			getAvgOrdersWaiters(){
				this.showChart=false;
				this.showChartMonth=false;
				axios.get('api/statistics/ordersWaiters').then(response=>{

					let totals=[];
					let users=[];

					for (var i = 0; i < response.data[1].length; i++) {
						users.push(response.data[1][i].resp);
						totals.push(response.data[1][i].total/response.data[0]);
					}

					this.dates=users;
					this.values=totals;
					this.mess='Average of Meals Handled By Day By Waiter';
					this.xx='Waiter Id';
					this.yy='Average Meals';
					this.showChart=true;

				}).catch(error=>{

				});
			},
			getTotalOrdersAndMealsByMonth(){
				this.showChart=false;
				this.showChartMonth=false;

				axios.get('api/statistics/ordersMealsByMonth').then(response=>{

					let monthsOrders=[];
					let monthsMeals=[];
					let valuesOrders=[];
					let valuesMeals=[];

					this.getMonthGeral(monthsOrders,response.data[0]);
					this.getMonthGeral(monthsMeals,response.data[1]);
					this.getTotalsGeral(valuesOrders,response.data[0]);
					this.getTotalsGeral(valuesMeals,response.data[1]);

					//mostrar o gráfico
					if(monthsOrders.length>=monthsMeals.length){
						this.dates=monthsOrders;
					}
					else{
						this.dates=monthsMeals;
					}

					this.values=valuesOrders;
					this.mess="Total Orders Handled";
					this.d2=valuesMeals;
					this.s2="Total Meals Handled";
					this.xx='Months';
					this.yy='Totals Orders / Meals';
					this.showChartMonth=true;
					
				}).catch(error=>{

				});
			},
			getMonthGeral(time,data){
				for (var i = 0; i < data.length; i++) {
					time.push(data[i].date);
				}
			},
			getTotalsGeral(totals,data){
				for (var i = 0; i < data.length; i++) {
					totals.push(data[i].total);
				}
			},
			getAvgTimeByMealByMonth(){
				this.showChart=false;
				this.showChartMonth=false;

				axios.get('api/statistics/timeMealsByMonth').then(response=>{
					let d=response.data[0];

					let times=response.data[1];

					let timediff=[];

					//Calcular o tempo que passou em minutos
					for (var i = 0; i < times.length; i++) {
						timediff.push(this.minutesBetweenDates(times[i].end,times[i].start));
					}

					//Somar o tempo total em cada mes
					let sum=[];

					let init=timediff[0];

					let index=0;
					let value=d[0].total;

					let k=0;

					for (var i = 0; i < timediff.length; i++) {
						if(i<value){
							k+=timediff[i];
							if(i==timediff.length-1){
								sum.push(k);
							}
						}
						else{
							sum.push(k);

							index++;
							value+=d[index].total;
							k=timediff[i];
						}
					}

					let avg = [];
					let months=[];

					for (var i = 0; i < sum.length; i++) {
						avg.push(sum[i]/d[i].total);
					}

					for (var i = 0; i < d.length; i++) {
						months.push(d[i].date);
					}

					this.dates=months;
					this.values=avg;
					this.mess='Average Minutes Meal Handled By Month';
					this.xx='Months';
					this.yy='Average Time In Minutes';
					this.showChart=true;
					
				}).catch(error=>{

				});
			},
			getAvgTimeByOrderItemsByMonth(){
				this.showChart=false;
				this.showChartMonth=false;

				if(this.month==''){
					this.showMessage=true;
					this.typeofmsg='alert-danger';
					this.message='Please select a month first';
					return;
				}

				axios.get('api/statistics/timeOrdersItemsByMonth/'+this.month).then(response=>{

					//Calcular os minutos de cada item
					let times=response.data[0];
					let items=response.data[1];

					let timediff=[];

					for (var i = 0; i < times.length; i++) {
						timediff.push(this.minutesBetweenDates(times[i].end,times[i].start));
					}

					//Somar minutos de cada item

					let sum=[];
					let init=timediff[0];
					let index=0;
					let value=items[0].total;

					let k=0;

					for (var i = 0; i < timediff.length; i++) {
						if(i<value){
							k+=timediff[i];
							if(i==timediff.length-1){
								sum.push(k);
							}
						}
						else{
							sum.push(k);

							index++;
							value+=items[index].total;
							k=timediff[i];
						}
					}

					//Fazer a média dos minutos por item

					let avg = [];
					let it=[];

					for (var i = 0; i < sum.length; i++) {
						avg.push(sum[i]/items[i].total);
					}

					for (var i = 0; i < items.length; i++) {
						it.push(items[i].item_id);
					}

					this.dates=it;
					this.values=avg;
					this.mess='Average Minutes Order Items Handled On Selected Month';
					this.xx='Item Id';
					this.yy='Average Time In Minutes';
					this.showChart=true;
					
				}).catch(error=>{

				});
			},

			minutesBetweenDates(end,start){
				let startTime = new Date(start); 
				let endTime = new Date(end);
				let difference = endTime.getTime() - startTime.getTime();
				return Math.round(difference / 60000);
			},
			close(){
				this.showMessage=false;
			},
		},
		mounted() {

			if(this.$store.state.user==null){
				this.$router.push({ path:'/login' });
				return;
			}

			this.getCooks();
			this.getWaiters();
			this.getMonths();
		},
		components: {
			'select-user':selectUser,
			'chart-orders-day':chartOrders,
			'chart-month-rest':chartMonth,
			'show-message':showMessage,
		},
	};
</script>

<style scoped>
.separator {
	border: 2px solid gray;
	border-radius: 5px;
}
</style>

