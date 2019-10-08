<template>
	<div class="card">
		<div class="card-body">

			<img :src="'storage/profiles/'+user.photo_url" alt="Item Photo" width="130" height="150" class="rounded mx-auto d-block">
			<p class="card-text"><strong>Name: </strong>{{user.name}}</p>
			<p class="card-text"><strong>Username: </strong>{{user.username}}</p>
			<p class="card-text"><strong>Email: </strong>{{user.email}}</p>
			<p class="card-text"><strong>Currently Working: </strong>{{user.shif_active==1?"Yes":"No"}}</p>
		</div>
	</div>
</template>


<script type="text/javascript">
	/*jshint esversion: 6 */

	export default {
		props:['user'],
	};
</script>