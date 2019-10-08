<template> 
	<div>
		<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

		<error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

		<div class="jumbotron">
            <h2>Confirm registration</h2>
       
            <div class="form-group">
				<label for="password" class="col-sm-4 col-form-label">Password</label>
				<div class="col-sm-10">
					<input type="password" name="password" class="form-control" v-model="password" id="password" placeholder="New Password">
				</div>
			</div>

			<div class="form-group">
				<label for="password_confirmation" class="col-sm-4 col-form-label">Password Confirmation</label>
				<div class="col-sm-10">
					<input type="password" name="password_confirmation" class="form-control" v-model="password_confirmation" id="password_confirmation" placeholder="Re-enter the password">
				</div>
				<small id="passwordHelp" class="form-text text-muted col-sm-offset-5 col-sm-6">The 'Confirmation Password' must be equal to 'New Password'</small>
			</div>

        </div>
            
		<div class="form-group">
        	<a class="btn btn-primary" v-on:click.prevent="confirmRegistration">Confirm</a>
		</div>
	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */

	import errorValidation from '../helpers/showErrors.vue';
	import showMessage from '../helpers/showMessage.vue';

	export default {
		props: ['user'],
		data() {
			return { 
				showMessage: false,
				message: "",
				errors: [],
				showErrors: false,
				typeofmsg: "",
				password: "",
				password_confirmation:"",
			};
		}, 
		methods: {
			confirmRegistration() {
				this.showMessage = false;
				this.showErrors = false;

				console.log(this.user);
				axios.patch('/api/users/confirmRegistration/' + this.user.id, {
					password: this.password, 
					password_confirmation: this.password_confirmation,
				}).then(response => {
					this.showErrors = false;
					this.showMessage = true;
					this.message = "Regist confirmed with success.";
					this.typeofmsg = "alert-success";
				    this.$router.go('/items');
				}).catch(error => {
					if(error.response.status==401){
						this.showMessage=true;
						this.message=error.response.data.unauthorized;
						this.typeofmsg= "alert-danger";
						return;
					}
					if(error.response.status == 422) {
						this.showErrors=true;
						this.showMessage = false;
						this.errors=error.response.data.errors;
					}
				});  

			}, close() {
				this.showErrors=false;
				this.showMessage=false;
			}
		}, components: {
			'error-validation': errorValidation, 
			'showMessage': showMessage,
		},
		mounted(){
			if(this.user==null){
				this.$router.push({ path:'/login' });
				return;
			}
		}
	};
</script>
