@extends('master')

@section('title', 'Restaurant Management')

@section('content')

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
		<div class="navbar-nav">
			<router-link class="nav-item nav-link" to="/items"><i class="fas fa-coffee">&nbsp;</i>Menu</router-link>
			<router-link class="nav-item nav-link" :to="{name: 'login',params:{isEmail:'true'}}" v-show="!this.$store.state.user"><i class='fas fa-user-alt'></i>&nbsp;Login With Email</router-link>
			<router-link class="nav-item nav-link" :to="{name: 'login',params:{isEmail:'false'}}" v-show="!this.$store.state.user"><i class='fas fa-user-alt'></i>&nbsp;Login With Username</router-link>
			<router-link class="nav-item nav-link" to="/changePassword" v-show="this.$store.state.user"><i class="fas fa-pencil-alt">&nbsp;</i>Update Password</router-link>
			<router-link class="nav-item nav-link" to="/profile" v-show="this.$store.state.user"><i class='fas fa-user-edit'>&nbsp;</i>Profile</router-link>
			<router-link class="nav-item nav-link" to="/workers" v-show="this.$store.state.user && this.$store.state.user.type=='manager'"><i class='fas fa-user-tie'>&nbsp;</i>Workers</router-link>
			<router-link class="nav-item nav-link" to="/me/orders" v-show="this.$store.state.user && this.$store.state.user.type=='cook'"><i class='fas fa-clipboard-list'>&nbsp;</i>My Orders</router-link>
			<router-link class="nav-item nav-link" to="/newMeal" v-show="this.$store.state.user && this.$store.state.user.type=='waiter'"><i class='fas fa-utensils'>&nbsp;</i>New Meal</router-link>
			<router-link class="nav-item nav-link" to="/meals" v-show="this.$store.state.user && this.$store.state.user.type=='waiter'"><i class="fas fa-clipboard-list">&nbsp;</i>Meals</router-link>
			<router-link class="nav-item nav-link" to="/newOrder" v-show="this.$store.state.user && this.$store.state.user.type=='waiter'"><i class="fas fa-edit">&nbsp;</i>New Order</router-link>
			<router-link class="nav-item nav-link" to="/orders" v-show="this.$store.state.user && this.$store.state.user.type=='waiter'"><i class="fas fa-clipboard-list">&nbsp;</i>Orders</router-link>
			<router-link class="nav-item nav-link" to="/invoices" v-show="this.$store.state.user && this.$store.state.user.type=='cashier'"><i class="fas fa-clipboard-list">&nbsp;</i>Invoices</router-link>
			<router-link class="nav-item nav-link" to="/tablesItems" v-show="this.$store.state.user && this.$store.state.user.type=='manager'"><i class='fas fa-utensils'>&nbsp;</i>Tables & Items</router-link>
			<router-link class="nav-item nav-link" to="/allMeals" v-show="this.$store.state.user && this.$store.state.user.type=='manager'"><i class="fab fa-apple">&nbsp;</i>Meals</router-link>
			<router-link class="nav-item nav-link" to="/dashboard" v-show="this.$store.state.user && this.$store.state.user.type=='manager'"><i class='fas fa-clipboard-list'>&nbsp;</i>Dashboard</router-link>
			<router-link class="nav-item nav-link" to="/stats" v-show="this.$store.state.user && this.$store.state.user.type=='manager'"><i class='fas fa-chart-line'>&nbsp;</i>Statistics</router-link>
			<router-link class="nav-item nav-link" to="/logout" v-show="this.$store.state.user"><i class='fas fa-user-times'>&nbsp;</i>Logout</router-link>
		</div>
	</div>

	<p class="pull-right text-light">
		Welcome @{{this.$store.state.user != null ? this.$store.state.user.name: '' }}!
	</p>
</nav>

<!--US6 component-->
<div v-if="this.$store.state.user">
	<start-quit></start-quit>
</div>

<div v-if="this.$store.state.user">
	<notifications></notifications>
</div>

<router-view> </router-view>

@endsection
@section('pagescript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js"></script>
<script src="js/app.js"></script>
@stop
