<template>
	<div class="card container">
		<div class="row" v-if="shiftActive==0">
			<div class="col-sm-4">
				<strong>Currently Working: No</strong>
			</div>

			<div class="col-sm-6" v-if="this.date!='Invalid date'">
				End of shift: {{date}} &nbsp; - Endend {{time}} ago
			</div>

			<div class="col-sm-2">
				<button class="btn btn-link btn-xs pull-right " @click.prevent="setStartShift">Start Shift</button>
			</div>
		</div>

		<div class="row" v-else>
			<div class="col-sm-4">
				<strong>Currently Working: Yes</strong>
			</div>
			<div class="col-sm-6" v-if="this.date!='Invalid date'">
				Start of shift: {{date}} &nbsp; - Started {{time}} ago
			</div>
			<div class="col-sm-2">
				<button class="btn btn-link btn-xs pull-right " @click.prevent="setEndShift">End Shift</button>
			</div>
		</div>
	</div>
</template>

<script type="text/javascript">
    /*jshint esversion: 6 */
    export default{
        data() {
            return {
                date:'',
                time:'',
                now:'',
                shiftActive:0,
                timer:'',
                dateToUpdate:'',
            };
        },
        methods:{
            getDate(){
                axios.get('api/users/dateShift/'+this.$store.state.user.id)
                    .then(response=>{

                        this.shiftActive=response.data.data.shift_active;

                        if(this.shiftActive==0){
                            this.dateToUpdate=moment(response.data.data.last_shift_end).format('YYYY-MM-DD HH:mm:ss');
                            this.date=moment(response.data.data.last_shift_end).format('YYYY-MM-DD HH:mm:ss');
                        }else{
                            this.dateToUpdate=moment(response.data.data.last_shift_start).format('YYYY-MM-DD HH:mm:ss');
                            this.date=moment(response.data.data.last_shift_start).format('YYYY-MM-DD HH:mm:ss');
                        }

                        if(this.date!='Invalid date')
                            this.updateTime();
                    }).
                catch(error=>{
                    if(error.response.status==401){
                        this.$router.push({ path:'/login' });
                    }
                });
            },
            setStartShift(){
                this.now=moment().format('YYYY-MM-DD HH:mm:ss');

                axios.patch('api/users/startShift/'+this.$store.state.user.id,
                    {
                        date:this.now,
                    }).
                then(response=>{
                    this.shiftActive=response.data.data.shift_active;

                    this.dateToUpdate=moment(response.data.data.last_shift_start);

                    this.date=moment(response.data.data.last_shift_start).format('YYYY-MM-DD HH:mm:ss');

                    this.$store.state.user.shift_active = 1;
                    let user =  this.$store.state.user;
                    this.$store.commit('setUser', user);
                    this.$socket.emit('start_shift', this.$store.state.user);
                }).
                catch(error=>{
                    if(error.response.status==401){
                        this.$router.push({ path:'/login' });
                    }
                });
            },
            setEndShift(){
                this.now=moment().format('YYYY-MM-DD HH:mm:ss');

                axios.patch('api/users/endShift/'+this.$store.state.user.id,
                    {
                        date:this.now,
                    }).
                then(response=>{
                    this.shiftActive=response.data.data.shift_active;
                    this.dateToUpdate=moment(response.data.data.last_shift_end);
                    this.date=moment(response.data.data.last_shift_end).format('YYYY-MM-DD HH:mm:ss');

                    this.$store.state.user.shift_active = 0;

                    let user =  this.$store.state.user;
                    this.$store.commit('setUser', user);
                    this.$socket.emit('end_shift', this.$store.state.user);
                }).
                catch(error=>{
                    if(error.response.status==401){
                        this.$router.push({ path:'/login' });
                    }
                });
            },
            updateTime(){
                this.now=moment();

                let miliseconds = moment(this.now,"YYYY-MM-DD HH:mm:ss").diff(moment(this.dateToUpdate,"YYYY-MM-DD HH:mm:ss"));
                let days = moment.duration(miliseconds);
                this.time = Math.floor(days.asHours()) + moment.utc(miliseconds).format(":mm:ss");
            },
        },
        mounted(){
            this.getDate();
        },
        created(){
            this.timer=setInterval(this.updateTime,2000);
        },
        beforeDestroy() {
            clearInterval(this.timer);
        }
    };
</script>