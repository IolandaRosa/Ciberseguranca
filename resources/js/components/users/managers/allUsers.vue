<template>
	<div>

		<div v-if="showUsers">
			<div class="inline-buttons">
				<a class="btn btn-info" v-on:click.prevent="getAllUsers">All users</a>
				<a class="btn btn-success" v-on:click.prevent="getUnBlockedUsers">Unblocked users</a>
				<a class="btn btn-info" v-on:click.prevent="getBlockedUsers">Blocked users</a>
				<a class="btn btn-danger" v-on:click.prevent="getDeletedUsers">Deleted users</a>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<router-link class="btn btn-success" to="/registerWorker" v-show="this.$store.state.user != null && this.$store.state.user.type == 'manager'"><i class='fas fa-user-plus'>&nbsp;</i>Register Worker</router-link>
			</div>
			<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>
			<error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>
			<div class="container-fluid">
				<div class="row">
					<div class="text-center">
						<users-list :users="users"  @user-edit="editUser" @user-block="blockUser" @user-unblock="unBlockUser" @user-delete="deleteUser"></users-list>
					</div>
				</div>
			</div>
		</div>
		<user-details v-if="showCreateEditUser"  :userToUpdate="userToUpdate" :isManagerProfile="false" @user-changed-click="userChanged" @cancel-user-click="cancelUser"></user-details>
	</div>
</template>

<script type="text/javascript">

	/*jshint esversion: 6 */

	import usersList from './usersList.vue';
	import userDetails from '../userDetails.vue';
	import showMessage from '../../helpers/showMessage.vue';
	import showError from '../../helpers/showErrors.vue';

	export default {
		data:
		function() {
			return {
				users:[],
				showCreateEditUser:false,
				isEdit:false,
				item:{},
				showUsers: true,
				userToUpdate: null,
				showMessage: false,
				typeofmsg: "",
				message:'',
				showErrors: false,
				errors: [],
			};
		},
		methods:{
			getAllUsers(){
				axios.get('api/users')
				.then(response=>{
					this.users = response.data.data;
				}).catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}

				});
			},
			getUnBlockedUsers(){
				axios.get('api/users/unBlocked')
				.then(response=>{
					this.users = response.data.data;
				}).catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}

				});
			},
			getDeletedUsers(){
				axios.get('api/users/deleted')
				.then(response=>{
					this.users = response.data.data;
				}).catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}

				});
			},
			getBlockedUsers(){
				axios.get('api/users/blocked')
				.then(response=>{
					this.users = response.data.data;
				}).catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}

				});
			},
			cancelUser(){
				this.showUsers=true;
				this.showCreateEditUser=false;
			},
			userChanged(){
				this.showMessage=true;
				this.message='User edited with success';
				this.typeofmsg= "alert-success";
				this.showUsers=true;
				this.showCreateEditUser=false;
				this.getAllUsers();
			},
			editUser(userId){
				axios.get('api/user/'+userId)
				.then(response=>{
					this.userToUpdate = response.data.data;
					this.showUsers=false;
					this.showCreateEditUser=true;
					this.isEdit=true;
				}).catch(error=>{
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}

				});
			},
			blockUser(userId){
				axios.patch('api/user/block/'+userId)
				.then(response=>{
					this.showMessage=true;
					this.message='User blocked with success';
					this.typeofmsg= "alert-success";
					this.getAllUsers();
				}).catch(error=>{
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
							this.message=error.response.data.user_already_blocked;
							this.typeofmsg= "alert-danger";
						}else{
							this.showMessage=false;
							this.showErrors=true;
							this.errors=error.response.data.errors;
						}
					}

				});

			},
			unBlockUser(userId){
				axios.patch('api/user/unBlock/'+userId)
				.then(response=>{

					this.showMessage=true;
					this.message='User unblocked with success';
					this.typeofmsg= "alert-success";
					this.getAllUsers();

				}).catch(error=>{
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
							this.message=error.response.data.user_already_unblocked;
							this.typeofmsg= "alert-danger";
						}else{
							this.showMessage=false;
							this.showErrors=true;
							this.errors=error.response.data.errors;
						}
					}

				});
			},
			deleteUser(userId){
				axios.delete('api/users/'+userId).
				then(response=>{
					this.getAllUsers();
				}).
				catch(error=>{
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
							this.message=error.response.data.user_cant_delete_himself;
							this.typeofmsg= "alert-danger";
						}else{
							this.showMessage=false;
							this.showErrors=true;
							this.errors=error.response.data.errors;
						}
					}
				});
			},
			close(){
				this.showMessage=false;
			},
		},
		mounted(){
			if(this.$store.state.user==null){
				this.$router.push({ path:'/login' });
				return;
			}
			this.getAllUsers();
		}, 
		components: {
			'users-list':usersList,
			'user-details': userDetails,
			'show-message':showMessage,
			'error-validation': showError,
		},
	};
</script>