<template>
    <div v-if="shiftActive==1">
        <hr>
        <div v-if="this.$store.state.user.type != 'manager'">
            <p><strong>Send a notification to all active managers pressing the enter key:</strong></p> 
        </div>
        <div v-else>
            <p><strong>Send a notification to other active managers pressing the enter key:</strong></p>
        </div>

        <div>
            <input type="text"  id="inputGlobal" class="inputchat" v-model="msgGlobalText" @keypress.enter="sendGlobalMsg">
        </div>

        
        <div v-if="this.$store.state.user.type == 'manager'">
            <textarea disabled="true" id="textGlobal" class="inputchat" v-model="msgGlobalTextArea">Global Chat</textarea>
        </div>
        
        <hr>
    </div>

</template>

<script type="text/javascript">
    /*jshint esversion: 6 */

    export default {
        data:
        function() {
            return {
                msgGlobalText: "",
                msgGlobalTextArea: "",
                shiftActive:"",
            };
        },
        methods: {
            sendGlobalMsg: function(){
                this.$socket.emit('msg_global', this.msgGlobalText, this.$store.state.user);
                this.msgGlobalText = "";
            },
        },
        sockets: {
            new_seasoning_order(dataFromServer){
                this.$toasted.info(dataFromServer);
            },
            msg_global_from_server(dataFromServer){
                this.msgGlobalTextArea = dataFromServer + '\n' + this.msgGlobalTextArea ;
            },
            msg_server_new_dish_order(dataFromServer){
                this.$toasted.info(dataFromServer, {
                    action: {
                        text : 'Go to orders list',
                        onClick : (e, toastObject) => {
                            this.$router.push({ path: '/me/orders' });
                        }
                    }
                });
            },
            user_ended_shift(dataFromServer){
                this.shiftActive = "0";
            },
            user_started_shift(dataFromServer){
                this.shiftActive = "1";
            },
            msg_server_dish_prepared(dataFromServer){
                this.$toasted.info(dataFromServer, {
                    action: {
                        text : 'Go to orders list',
                        onClick : (e, toastObject) => {
                            this.$router.push({ path: '/orders' });
                        }
                    }
                });
            },
            msg_server_dish_assigned_to_cook(dataFromServer){
                this.$toasted.info("The order: " + dataFromServer[1] + " was ASSIGNED to cook: " + dataFromServer[0].name, {
                    action: {
                        text : 'Go to orders list',
                        onClick : (e, toastObject) => {
                            this.$router.push({ path: '/orders' });
                        }
                    }
                });
            },
            new_pending_invoice(invoice) {
                this.$toasted.info("A new pending invoice was created for the meal: " + invoice.meal_id + " with the id: " + invoice.id + " and total price: " + invoice.total_price, {
                    action: {
                        text : 'Go to invoices list',
                        onClick : (e, toastObject) => {
                            this.$router.push({ path: '/invoices' });
                        }
                    }
                });
            },
            invoice_paid(dataFromServer) {
                this.$toasted.info("The invoice "+ dataFromServer[1].id + " for the meal " + dataFromServer[1].meal_id + " was marked as paid by " +  dataFromServer[0].name + " (ID: " + dataFromServer[0].id + ").", {
                    action: {
                        text : 'Go to invoices list',
                        onClick : (e, toastObject) => {
                            this.$router.push({ path: '/dashboard' });
                        }
                    }
                });
            },
            meal_terminated(meal) {
                this.$toasted.info("The meal " + meal.id + " was terminated.", {
                    action: {
                        text : 'Go to meals list',
                        onClick : (e, toastObject) => {
                            this.$router.push({ path: '/allMeals' });
                        }
                    }
                });
            }
        }, mounted() {
            if(this.$store.state.user==null){
                this.$router.push({ path:'/login' });
                return;
            }
            this.shiftActive = this.$store.state.user.shift_active;
        },
    };
</script>
