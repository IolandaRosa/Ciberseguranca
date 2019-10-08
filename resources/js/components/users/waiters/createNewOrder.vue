<template>
  <div >
    <div class="jumbotron">
      <h1>New Order</h1>
    </div>
    <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

    <error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

    <div class="jumbotron">

      <div class="form-group">
        <label for="state">State</label>
        <input type="text" class="form-control" name="state" id="state"  v-model="state" readonly/>

        <label for="selectSeasoning">Seasoning</label>
        <select v-model="seasoning" id="selectSeasoning" name="selectSeasoning" class="form-control">
          <option disabled value=''>Please select the seasoning</option>
          <option value='unsalty'>Unsalty</option>
          <option value='salty'>Salty</option>
          <option value='spicy'>Spicy</option>
          <option value='sweet'>Sweet</option>
        </select>


        <label >Items: </label>
        <item-list :items="items" :showSelected="true" v-on:selectedRow="selectedItem = $event"> </item-list>


        <label >Meals: </label>
        <meals-list :meals="meals"  v-on:selectedRow="selectedMeal = $event"> </meals-list>

      </div>

      <div class="form-group">
        <a class="btn btn-primary" v-on:click.prevent="createOrder">Create Order</a>
      </div>
    </div>

  </div>
</template>



<script type="text/javascript">
  /*jshint esversion: 6 */

  import errorValidation from '../../helpers/showErrors.vue';
  import showMessage from '../../helpers/showMessage.vue';
  import itemList from '../../items/itemList.vue';
  import mealsList from './mealsList.vue';

  export default{
    data() {
      return {
        showMessage: false,
        message: "",
        errors: [],
        showErrors: false,
        typeofmsg: "",
        state: 'pending',
        tableSelected: '',
        user: this.$store.state.user,
        tables: [],
        items: [],
        meals: [],
        selectedMeal: '',
        selectedItem: '',
        orderId: '',
        seasoning:'',
      };
    },
    methods:{
      createOrder() {
        this.showMessage = false;
        this.showErrors = false;

        const formData = new FormData();
        formData.append('state', this.state);
        formData.append('seasoning',this.seasoning);

        console.log(this.seasoning);

        if(this.selectedItem === '') {
          formData.append('item_id', '');
        } else{
          formData.append('item_id', this.items[this.selectedItem].id);
          formData.append('total_price_preview', this.items[this.selectedItem].price);
          this.$socket.emit('new_dish_order', this.items[this.selectedItem].name, this.$store.state.user, this.seasoning);
        }

        if(this.selectedMeal === '')
        {
          formData.append('meal_id', '');
        } else {
          formData.append('meal_id', this.meals[this.selectedMeal[0]].id);
        }

        axios.post('api/orders/createOrder', formData).then(response => {
          axios.put('api/meals/totalPrice',
          {
            meal_id:this.meals[this.selectedMeal[0]].id,
            total_price_preview:this.items[this.selectedItem].price,
          }).then(response => {
          }).catch(error => {
            if(error.response.status == 422) {
             this.showErrors=true;
             this.showMessage=false;
             this.typeofmsg= "alert-danger";
             this.errors=error.response.data.errors;
           }
         });

          this.showErrors = false;
          this.showMessage = true;
          this.message = "Order created with success.";
          this.typeofmsg = "alert-success";
          this.orderId = response.data.data.id;

          console.log(response.data.data);

          this.$router.push({name: 'waiterOrders', params: {orderId: this.orderId,refresh5Seconds: true}});
        }).catch(error => {
          if(error.response.status == 422) {
            this.showErrors=true;
            this.showMessage=false;
            this.typeofmsg= "alert-danger";
            this.errors=error.response.data.errors;
          }
        });

      },getItems: function() {
       axios.get('api/items')
       .then(response=>{this.items = response.data.data;
       });

     },getMeals: function() {
       axios.get('api/meals/myMeals/'+this.user.id)
       .then(response=>{this.meals = response.data.data;
       });

     },
     close(){
       this.showErrors=false;
       this.showMessage=false;
     },
   },
   mounted(){
    this.state = "pending";
    this.getItems();
    this.getMeals();

  },
  sockets:{
    refresh_items(){
     this.getItems();
   }
 },
 components: {
  'error-validation':errorValidation,
  'show-message':showMessage,
  itemList,
  mealsList,
},

};
</script>