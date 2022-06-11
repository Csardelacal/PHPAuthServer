<template>
	
<form class="regular" method="POST">
	
	<div class="max-w-md mx-auto">
		<div class="mx-auto rounded-md bg-white border-solid border border-slate-100 relative shadow">
			<div class="p-6 ">
				
				
				<div >
					<label class="block text-gray-700 text-sm font-bold mb-2" for="email">
						Current email address
					</label>
					<div 
						class="flex justify-between shadow appearance-none border rounded w-full py-2 px-3 text-gray-500 leading-tight focus:outline-none focus:shadow-outline transition:filter" 
						type="text" >
						<span :class="[hideCurr? 'blur-sm':'blur-none']">{{currentEmail}}</span>
						<span class="text-sky-600 cursor-pointer" @click="hideCurr = !hideCurr">{{hideCurr? 'Show' : 'Hide'}}</span>
					</div>
				</div>
				
				<div class="h-4"></div>
				<div >
					<label class="block text-gray-700 text-sm font-bold mb-2" for="email">
						New email address
					</label>
					<input 
						class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
						type="text" 
						id="email"
						name="email" 
						placeholder="example@acme.com" v-model="email">
				</div>
				
				<div class="h-4"></div>
				
				<div>
					<label class="block text-gray-700 text-sm font-bold mb-2" for="password">
						Password
					</label>
					<input 
						class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
						type="password" 
						id="password"
						name="password" 
						placeholder="*********" 
						v-model="passw">
				</div>
				
				<div class="h-6"></div>
				
				<div class="text-right">
					<button type="submit"
						class="font-bold text-white py-2 px-4 rounded transition duration-300 ease-in-out transition-background-color" 
						:class="[disabled? 'bg-slate-300 text-slate-200 cursor-not-allowed' : 'bg-sky-400 drop-shadow-md cursor-pointer']" :disabled="disabled === true">
						Store
					</button>
				</div>
			</div>
		</div>
		
		<div class="h-4"></div>
		
		<p class="text-center text-gray-500 text-xs">
			You can use this form to update your email address. Your email address is used
			to send you notifications. 
			<strong>
				Your new email will need to be verified again.
			</strong>
		</p>
	</div>
</form>

</template>

<style scoped>
</style>

<script setup>
import { ref, watch } from 'vue';
import isEmail from 'validator/es/lib/isEmail';

const props = defineProps({
	currentEmail: String
});

const email = ref('');
const passw = ref('');

const disabled = ref(true);
const hideCurr = ref(true);


watch([email, passw], async (value) => {
	
	const conditions = [
		email.value !== props.currentEmail,
		email.value !== '',
		passw.value.length >= 8,
		isEmail(email.value)
	];
	
	for (let i = 0; i < conditions.length; i++) {
		if (!conditions[i]) {
			disabled.value = true;
			return;
		}
	}
	
	disabled.value = false;
});
</script>
