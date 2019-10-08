<template>
    <div >
        <div class="jumbotron">
            <h1>Meals Summary</h1>
        </div>
        <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

        <error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

        <div class="jumbotron">
            <label >Meals: </label>
            <meals-list :meals="meals"  v-on:selectedRow="getOrders($event)"   v-on:terminateOrder="getOrders($event)" :terminate="true"> </meals-list>

            <label >Orders: </label>
            <orders-list :orders="orders" :isAll="true"  :isWaiter="true" v-if="this.$store.state.user.type=='waiter'"  :ordersSummary="true"></orders-list>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Meal termination</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        There is at least one order that is not delivered do you still want to terminate this meal?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                        <button type="button" class="btn btn-primary" @click="terminateOrder">Yes</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>



<script type="text/javascript">
    /*jshint esversion: 6 */

    import errorValidation from '../../helpers/showErrors.vue';
    import showMessage from '../../helpers/showMessage.vue';
    import mealsList from './mealsList.vue';
    import cookOrdersList from '../cooks/cookOrdersList.vue';


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
                meals: [],
                orderId: '',
                selectedMeal: '',
                orders: [],
                showConfirmationDialog: false,
                modal: false,
                values: null,
                invoice: null,
            };
        },
        methods:{
            getOrders: function(values) {

                this.values = values;
                if(values[2] == false)
                {
                    this.selectedMeal = this.meals[values[0]].id;
                }else {
                    this.selectedMeal = [values[0]];
                }
                axios.get('api/orders/ordersOfaMeal/'+this.selectedMeal)
                    .then(response=>{
                        //console.log('orders: ' +response.data.data + 'selected meals: ' + this.selectedMeal);
                        this.orders = response.data.data;
                        if(values[1] == true)
                        {
                            for (let i = 0; i < this.orders.length; i++) {
                                if(this.orders[i].state != 'delivered')
                                {
                                    $('#confirmationModal').modal('toggle');
                                    return;
                                }
                            }
                            this.terminateOrder();
                        }
                    });

            },getMeals: function() {
                axios.get('api/meals/myMeals/'+this.user.id)
                    .then(response=>{this.meals = response.data.data;
                    });

            }, terminateOrder: function() {
                let mealId = this.meals[this.values[0]];
                $('#confirmationModal').modal('hide');
                axios.patch('api/meals/terminateMeal/' + mealId.id).then(response => {
                    this.showErrors = false;
                    this.showMessage = true;
                    this.message = "Meal terminated with success.";
                    this.typeofmsg = "alert-success";
                    this.getMeals();
                    this.$socket.emit("mealTerminated");
                    this.orders = [];

                }).catch(error => {
                    if(error.response.status == 422) {
                        this.showErrors=true;
                        this.showMessage=false;
                        this.typeofmsg= "alert-danger";
                        this.errors=error.response.data.errors;
                    }
                });

                axios.post('api/invoices/create/' + mealId.id).then(response => {
                    this.showErrors = false;
                    this.$socket.emit("createdNewInvoice", response.data.data, mealId);
                    this.showMessage = true;
                    this.invoice = response.data.data;

                    axios.post('api/invoiceItems/create/' + mealId.id + '/' + this.invoice.id).then(response => {
                        this.showErrors = false;
                        this.showMessage = true;
                    }).catch(error => {
                        if(error.response.status == 422) {
                            this.showErrors=true;
                            this.showMessage=false;
                            this.typeofmsg= "alert-danger";
                            this.errors=error.response.data.errors;
                        }
                    });

                }).catch(error => {
                    if(error.response.status == 422) {
                        this.showErrors=true;
                        this.showMessage=false;
                        this.typeofmsg= "alert-danger";
                        this.errors=error.response.data.errors;
                    }
                });

            },
            close(){
                this.showErrors=false;
                this.showMessage=false;
            },
        },sockets: {
            refresh_orders_summary(dataFromServer){
                if(this.selectedMeal == dataFromServer)
                {
                    this.getOrders([dataFromServer,false,true]);
                }
            },
        },
        mounted(){
            this.state = "pending";
            this.getMeals();
        },
        components: {
            'error-validation':errorValidation,
            'show-message':showMessage,
            'meals-list': mealsList,
            'orders-list':cookOrdersList,
        },
        created(){
        }
    };
</script>