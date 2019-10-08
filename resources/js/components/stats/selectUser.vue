<template>
	<div>
		
		<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

		<div class="form-row">
			<div class="col" align="right">
				<strong>Please select a user: &nbsp;</strong>
			</div>
			<div class="col">
				<select v-model="user" id="selectUser" name="selectUser" class="form-control mb-2 mr-sm-2">
					<option v-for="user in users" :value="user.id"> {{user.name}} </option>
				</select>
			</div>
			<div class="col">
				<button class="btn btn-outline-info btn-xs mb-2" @click="viewOrderStats"><i class="fas fa-search"></i>View % Orders Handled by Day by User</button>
			</div>
		</div>			
	</div>

</template>

<script type="text/javascript">
	/*jshint esversion: 6 */

	import showMessage from '../helpers/showMessage.vue';

	export default {
		props:['users','message'],
		data: function() {
			return {
				user:null,
				showMessage:false,
				typeofmsg: "",	
			};
		},
		methods: {
			viewOrderStats(){
				this.showMessage=false;
				if(this.user==null){
					this.showMessage=true;
					this.typeofmsg='alert-danger';
					return;
				}
				let type='cook';
				if(this.show==true){
					type='waiter';
				}
				this.$emit('orders-stats',this.user);
			},
			close(){
				this.showMessage=false;
			},


		},
		components: {
			'show-message':showMessage,
		},
		
	};
</script>