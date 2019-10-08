<template>
	<div>
		<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

		<error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

		<div class="jumbotron">
            <h2>Regist worker</h2>
            <div class="form-group">
            	<label for="inputName">Name</label>
            	<input type="text" class="form-control" name="name" id="inputName" placeholder="Worker name" v-model="workerName"/>

            	<label for="inputUsername">Username</label>
                <input type="username" class="form-control" name="username" id="inputUsername" placeholder="Worker username" v-model="username"/>

                <label for="inputEmail">Email</label>
                <input type="email" class="form-control" name="email" id="inputEmail" placeholder="Email address" v-model="email"/>

                <label for="selectType">Type of worker</label>
                <select v-model="typeSelected" id="selectType" name="selectType" class="form-control">
                	<option disabled value="">Please select the type of worker</option>
                	<option v-for="type in typesOfWorkers" v-bind:value="type.value"> {{ type.text }} </option>
                </select>

               	
                 <file-upload v-on:fileChanged="onFileChanged"> </file-upload>          	
				

            </div>
            
            <div class="form-group">
                <a class="btn btn-primary" v-on:click.prevent="registerWorker">Register</a>
            </div>
        </div>

	</div>	
</template>	



<script>
/*jshint esversion: 6 */

	import errorValidation from '../helpers/showErrors.vue';
	import showMessage from '../helpers/showMessage.vue';
	import fileUpload from '../helpers/uploadFile.vue';

	export default {
		data() {
			return { 
				typesOfWorkers: [ {text: 'Manager', value: 'manager'}, {text: 'Cook', value: 'cook'}, { text: 'Cashier', value:'cashier'}, {text: 'Waiter', value:'waiter'}],
				showMessage: false,
				message: "",
				errors: [],
				showErrors: false,
				typeofmsg: "",
				workerName: '',
				email: '',
				username: '',
				typeSelected: '',
				file: '',
			};
		}, 
		methods: {
			registerWorker() {
				this.showMessage = false;
				this.showErrors = false;

				const formData = new FormData();
				formData.append('photo', this.file);
				formData.append('name', this.workerName);
				formData.append('email', this.email);
				formData.append('username', this.username);
				formData.append('type', this.typeSelected);

				axios.post('api/users/registerWorker', formData).then(response => {
					this.showErrors = false;
					this.showMessage = true;
					this.message = "Worked registered with success.";
					this.typeofmsg = "alert-success";
				}).catch(error => {
					if(error.response.status == 422) {
						this.showErrors=true;
						this.showMessage = false;
						this.errors=error.response.data.errors;
					}
				});   

			}, close() {
				this.showErrors=false;
				this.showMessage=false;
			}, onFileChanged(fileSelected) {
				this.file = fileSelected
			}
		}, components: {
			'error-validation': errorValidation, 
			'showMessage': showMessage,
			'file-upload': fileUpload,
		},
		mounted() {
			if (this.$store.state.user.type != "manager") {
	            // se não for manager volta para a pág inicial
	            next('/items');
	            return;
        	}
		}
	};
	

</script>