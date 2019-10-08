<template>
	<div>
		<div class="jumbotron">
			<h1>Update Password</h1>
		</div>
		<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

		<error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

		<div>
			<div class="form-group">
				<label for="oldPassword" class="col-sm-4 col-form-label"> Current Password</label>
				<div class="col-sm-10">
					<input type="password" name="old_password" class="form-control" id="oldPassword" v-model="old_password"/>
				</div>
			</div>
			
			<div class="form-group">
				<label for="password" class="col-sm-4 col-form-label"> New Password</label>
				<div class="col-sm-10">
					<input type="password" name="password" class="form-control" v-model="password" id="password" placeholder="New Password">
				</div>
			</div>

			<div class="form-group">
				<label for="passwordConfirmation" class="col-sm-4 col-form-label"> Password Confirmation</label>
				<div class="col-sm-10">
					<input type="password" name="password_confirmation" class="form-control" v-model="password_confirmation" id="passwordConfirmation" placeholder="Re-enter New Password" aria-describedby="emailHelp">
				</div>
				<small id="passwordHelp" class="form-text text-muted col-sm-offset-5 col-sm-6">The 'Confirmation Password' must be equal to 'New Password'</small>
			</div>
			<div class="form-group">
				<a class="btn btn-primary" @click.prevent="updatePassword">Update Password</a>
			</div>
		</div>
	</div>   
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */
	import errorValidation from '../helpers/showErrors.vue';
	import showMessage from '../helpers/showMessage.vue';

	export default{
		data() {
			return {
				showMessage:false,
				message:'',
				errors: [],
				showErrors:false,
				old_password:'',
				password:'',
				password_confirmation:'',
				typeofmsg: "",
			};
		},
		methods:{
			updatePassword(){
				this.showMessage=false;
				this.showErrors=false;

				axios.patch('api/users/password/'+this.$store.state.user.id, 
				{ 
					old_password:this.old_password,
					password_confirmation:this.password_confirmation,
					password:this.password,
				}).
				then(response=>{
					this.showErrors=false;
					this.showMessage=true;
					this.message='Password updated with success';
					this.typeofmsg= "alert-success";
					this.$router.push({ path:'/items' });
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
							this.message=error.response.data.old_password;
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
				this.showErrors=false;
				this.showMessage=false;
			}
		},
		components: {
			'error-validation': errorValidation,
			'show-message': showMessage,
		},
		mounted(){
			if(this.$store.state.user==null){
				this.$router.push({ path:'/login' });
				return;
			}
		}		
	};
</script>