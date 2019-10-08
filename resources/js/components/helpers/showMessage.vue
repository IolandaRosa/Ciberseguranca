<template>
	<div class="alert" :class="typeofmsg" v-if="showSuccess">             
		<button type="button" class="close" @click="close">&times;</button>
		<strong>{{ successMessage }}</strong>
	</div>
</template>

<script>
	/*jshint esversion: 6 */

	export default{
		props: ['successMessage','showSuccess','typeofmsg'],
		methods:{
			close(){
				this.$emit('close');
			},
		},
	};
</script>