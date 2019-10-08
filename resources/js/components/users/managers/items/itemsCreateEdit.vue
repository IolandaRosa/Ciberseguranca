<template>
	<div>
		<show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

		<error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

		<h5 class="text-center"><strong>{{this.isEdit==true?'Update Item':'New Item'}}</strong></h5>
		<br>

		<div v-if="isEdit">
			<img :src="'storage/items/'+item.photo_url" alt="Item Photo" width="100" height="100" class="rounded mx-auto d-block">
			<br>
		</div>

		<div class="form-group row">
			<label for="name" class="col-sm-2 col-form-label">Name</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" id="name" v-model="name">
			</div>
		</div>
		<div class="form-group row">
			<label for="description" class="col-sm-2 col-form-label">Description</label>
			<div class="col-sm-10">
				<textarea type="text" class="form-control" id="description" v-model="description"></textarea>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-6">
				<label for="price">Price</label>
				<input type="text" class="form-control" id="price" v-model="price">
			</div>
			<div class="form-group col-md-6">
				<label for="type">Type</label>
				<select class="form-control" id="type" v-model="type">
					<option value="dish">Dish</option>
					<option value="drink">Drink</option>>
				</select>
			</div>
		</div>

		<div class="form-group">
			<file-upload @fileChanged="onFileChanged"> </file-upload>
		</div>

		<div class="form-inline">
			<div v-if="isEdit">
				<button class="btn btn-outline-info mb-2" @click.prevent="editItem">Update</button>
			</div>
			<div v-else>
				<button class="btn btn-outline-info mb-2" @click.prevent="createItem">Save</button>
			</div>
			&nbsp;
			<button  class="btn btn-outline-danger mb-2" @click.prevent="cancelItem">Cancel</button>
		</div>
	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */

	import errorValidation from '../../../helpers/showErrors.vue';
	import showMessage from '../../../helpers/showMessage.vue';
	import uploadFile from '../../../helpers/uploadFile.vue';

	export default {

		props:['isEdit','item'],
		data:
		function() {
			return {
				name:'',
				description:'',
				price:'',
				type:'',
				showErrors:false,
				showMessage:false,
				message:'',
				typeofmsg:'',
				errors:[],
				file:'',
			};
		},
		methods:{
			createItem(){
				this.showMessage=false;
				this.showErrors=false;

				const formData = new FormData();

				if(this.file != null)
				{
					formData.append('photo_url', this.file);

				}
				formData.append('name', this.name);
				formData.append('description', this.description);
				formData.append('price', this.price);
				formData.append('type', this.type);

				axios.post('api/items', formData)
				.then(response => {
					this.$emit('items-changed-click');
					this.$socket.emit('inform-item-alteration', this.$store.state.user,response.data.data.id);
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
			editItem(){
				this.showMessage=false;
				this.showErrors=false;

				const formData = new FormData();

				if(this.file != null)
				{
					formData.append('photo_url', this.file);

				}
				formData.append('name', this.name);
				formData.append('description', this.description);
				formData.append('price', this.price);
				formData.append('type', this.type);
				formData.append('_method', 'put');

				axios.post('api/items/'+this.item.id, formData)
				.then(response => {
					console.log("resp");
					this.$emit('items-changed-click');
					this.$socket.emit('inform-item-alteration', this.$store.state.user,response.data.data.id);
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
			cancelItem(){
				this.$emit('cancel-click');
			},
			close(){
				this.showErrors=false;
				this.showMessage=false;
			},
			onFileChanged(fileSelected) {
				this.file = fileSelected;
			},
		},
		components: {
			'error-validation':errorValidation,
			'show-message':showMessage,
			'file-upload':uploadFile,
		},
		mounted(){
			if(this.$store.state.user==null){
				this.$router.push({ path:'/login' });
				return;
			}

			if(this.isEdit){
				this.name=this.item.name;
				this.description=this.item.description;
				this.price=this.item.price;
				this.type=this.item.type;
				document.getElementById('type').value = this.type;
			}

		}, 
	};
</script>