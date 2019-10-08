<template>

    <div>
        <div class="jumbotron">
            <h1>DashBoard</h1>
        </div>

        <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

        <label> <h4>Invoices: </h4> </label>
        <invoices-list  :isManagerDashboard="true" :showSelected="false" v-on:invoice-not-paid="markInvoiceAsNotPaid" v-on:show-details="showDetails"> </invoices-list>

        <label> <h4>Active or Termitaned Meals: </h4> </label>
        <meals-list :meals="meals" :isManagerDashboard="true"  :terminate="true" v-on:selectedRow="refreshOrdersList($event)" v-on:meal-not-paid="markMealAsNotPaid"> </meals-list>

        <label> <h4>Orders: </h4> </label>
        <orders-list v-if="showOrders" :orders="orders" :isAll="true" :isAssignTocook="true" :ordersSummary="true" :isWaiter="false" ></orders-list>

        <!-- Modal for the manager -->
        <div class="modal fade" id="invoiceDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Invoice Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" v-if="showingDetails">
                        <p class="textLabel">ID: {{ invoice.id }} </p>
                        <p class="textLabel">State: {{ invoice.state }} </p>
                        <p class="textLabel">Meal id: {{ invoice.meal_id }} </p>
                        <p class="textLabel">Date: {{ invoice.date }} </p>
                        <p class="textLabel">Total price: {{ invoice.total_price }} </p>
                        <p class="textLabel">Responsible Waiter Id: {{ invoice.responsible_waiter_id }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Name:  {{ invoice.waiterName }}</p>

                        <label>Items:</label>
                        <items-list :items="invoiceItems"> </items-list>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>




    </div>

</template>

<script>
    /*jshint esversion: 6 */
    import pendingInvoicesList from '../cashiers/listPendingInvoices.vue';
    import invoicesList from '../cashiers/listInvoices.vue';
    import invoiceDetails from '../cashiers/invoiceDetails.vue';
    import invoiceItemsList from '../cashiers/invoiceItemsList.vue';
    import errorValidation from '../../helpers/showErrors.vue';
    import showMessage from '../../helpers/showMessage.vue';
    import mealsList from '../waiters/mealsList.vue';
    import ordersList from '../cooks/cookOrdersList.vue';

    export default {
        data:
        function() {
            return {
                showMessage: false,
                pendingInvoices: [],
                showingDetails: false,
                errors: [],
                message: "",
                showErrors: false,
                typeofmsg: "",
                meals: [],
                orders: [],
                showOrders: false,
                invoice: null,
                currentMealId: null,
                invoiceItems: [],
            };
        },
        methods: {
            getMeals: function() {

                axios.get('api/meals/activeOrTeminatedMeals')
                .then(response=>{this.meals = response.data.data;
                });
            },
            showDetails: function(invoiceDetails) {
                this.showingDetails = true;
                this.invoice = invoiceDetails;
                this.getInvoiceItems();
                $('#invoiceDetails').modal('toggle');
            },
            markInvoiceAsNotPaid: function(invoiceDetails) {
                this.invoice = invoiceDetails;

                 axios.patch('api/invoices/state/'+this.invoice.id,
                 {
                    state:'not paid',
                }).
                 then(response=>{
                    
                    axios.patch('api/meals/notPaid/'+this.invoice.meal_id).
                    then(response=>{
                        this.getMeals();
                        this.$socket.emit('notPaidInvoiceMeal');
                        if(this.meals.length == 1)
                        {
                            this.orders = [];
                        }
                    }).
                    catch(error=>{
                        if(error.response.status==422){
                            this.showMessage=true;
                            this.message=error.response.data.error;
                            this.typeofmsg= "alert-danger";
                        }
                    });

                }).
                 catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });

             },
             markMealAsNotPaid: function(mealDetails) {
                axios.patch('api/meals/notPaid/'+mealDetails,
                {
                }).
                then(response=>{
                    this.getMeals();
                    if(response.data.data[0].id != null)
                    {
                        axios.patch('api/invoices/state/'+response.data.data[0].id,
                        {
                            state:'not paid',
                        }).
                        then(response=>{
                            this.orders = [];
                            this.$socket.emit('notPaidInvoiceMeal');
                       }).
                        catch(error=>{
                            if(error.response.status==422){
                                this.showMessage=true;
                                this.message=error.response.data.error;
                                this.typeofmsg= "alert-danger";
                            }
                        });
                    }
                }).
                catch(error=>{
                    if(error.response.status==422){
                        this.showMessage=true;
                        this.message=error.response.data.error;
                        this.typeofmsg= "alert-danger";
                    }
                });

            },
            refreshOrdersList: function(dataFromMealList) {
                this.currentMealId = dataFromMealList[3];
                axios.get('api/orders/ordersOfaMeal/'+dataFromMealList[3])
                .then(response=>{this.orders = response.data.data;
                    this.showOrders = true;

                });
            },
            close(){
                this.showErrors=false;
                this.showMessage=false;
            },
            getInvoiceItems: function() {
                axios.get('api/invoiceItems/items/' + this.invoice.id)
                .then(response=>{
                    this.invoiceItems = response.data.data;
                });
            },
        },
        mounted() {
            this.getMeals();
        },
        components: {
            pendingInvoicesList,
            invoiceDetails,
            'invoices-list': invoicesList,
            'show-message': showMessage,
            'error-validation': errorValidation,
            'meals-list': mealsList,
            'orders-list':ordersList,
            'items-list': invoiceItemsList,
        },
        sockets: {
            //se a tabela meals passar a usar paginação do lado do servidor entao estes sockets vao para o componenete das mealslist assim como a listIncoices tem
            refresh_meals() {
                this.getMeals();
            },
            meal_terminated() {
                this.getMeals();
            },
            refresh_invoice_meals() {
                this.getMeals();
            },
            invoice_paid() {
                this.getMeals();
            },
            inform_alterations_unsigned_orders(serverData) {
                if(serverData === this.currentMealId)
                {
                    this.refreshOrdersList([0,0,0,serverData]);
                }
            }

        }

    };
</script>

<style scoped>
p.textLabel {
    font-weight: bold;
}

</style>