<template>
	<div>
		<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

		<error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

		<div class="form-inline">
			<label for="tableNumber" class="mr-sm-2"><strong>Insert A New Table Number:</strong></label>
			<input type="text" id="tableNumber" class="form-control mb-2 mr-sm-2" v-model="tableNumber">
			<div v-if="isEdit">
				<button class="btn btn-outline-info mb-2" @click.prevent="editTable">Update</button>
			</div>
			<div v-else>
				<button class="btn btn-outline-info mb-2" @click.prevent="createTable">Save</button>
			</div>
			&nbsp;
			<button  class="btn btn-outline-danger mb-2" @click.prevent="cancelEditTable">Cancel</button>
		</div>
	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */
	import errorValidation from '../../../helpers/showErrors.vue';
	import showMessage from '../../../helpers/showMessage.vue';

	export default {
		props:['table','isEdit'],
		data:
		function() {
			return {
				tableNumber:'',
				showErrors:false,
				showMessage:false,
				message:'',
				typeofmsg:'',
				errors:[],
			};
		},
		methods:{
			createTable(){
				this.showErrors=false;
				this.showMessage=false;
				axios.post('api/tables', 
				{ 
					'table_number':this.tableNumber,
				}).
				then(response=>{
					this.$socket.emit('inform-managers-waiters-new-table', this.$store.state.user,this.tableNumber);
					this.$emit('table-changed-click');
				}).
				catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}
					if(error.response.status==422){
						this.showMessage=false;
						this.showErrors=true;
						this.errors=error.response.data.errors;
					}
				});
			},
			editTable(){
				this.showErrors=false;
				this.showMessage=false;
				axios.put('api/tables/'+this.table, 
				{ 
					'table_number':this.tableNumber,
				}).
				then(response=>{
					console.log(response.data.data);
					this.$socket.emit('inform-managers-waiters-new-table', this.$store.state.user,this.tableNumber);
					this.$emit('table-changed-click');
				}).
				catch(error=>{
					console.log(error.response);
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}
					if(error.response.status==422){
						if(error.response.data.errors==undefined){
							this.showErrors=false;
							this.showMessage=true;
							this.message=error.response.data.error;
							this.typeofmsg= "alert-danger";
						}else{
							this.showMessage=false;
							this.showErrors=true;
							this.errors=error.response.data.errors;
						}
					}
				});
			},
			cancelEditTable(){
				this.$emit('cancel-table-click');
			},
			close(){
				this.showErrors=false;
				this.showMessage=false;
			}
		},
		components: {
			'error-validation':errorValidation,
			'show-message':showMessage,
		},
		mounted(){
			if(this.$store.state.user==null){
				this.$router.push({ path:'/login' });
				return;
			}

			this.tableNumber=this.table;
		}, 
	};
</script>