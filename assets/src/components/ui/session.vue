<template>
	<tr class="border-t border-gray-200 rounded bg-white p-4 mt-4 text-gray-500">
		<td class="flex items-center gap-3">
			<img :src="user.avatar" class="w-10 h-10 rounded-full shadow">
			<div >
				<span class="font-bold text-gray-800">{{user.username}}</span>
				<div class="flex items-center">
					<LanguageIcon class="w-4 h-4"></LanguageIcon>
					<span>{{ language || 'Unknown' }}</span>
				</div>
			</div>
		</td>
		
		<td>
			<div class="flex items-center">
				<a :href="`/session/location/${location.country}`" class="leading-tight">
					<Flag :country="location.country" style="margin: 0"></Flag>
				</a>
				<a :href="`/session/location/${location.country}/${_btoa(location.city)}`">
					<span class="ml-2">{{ location.city || 'Unknown' }}</span>
				</a>
			</div>
		</td>
		
		<td>
			<div class="flex items-center" v-if="ip !== ''">
				<FingerPrintIcon class="w-4 h-4"></FingerPrintIcon>
				<a :href="`/session/ip/${ip}`" class="ml-2">IP Address</a>
				
				<span  v-if="vpn" class="inline-flex items-center bg-indigo-600 text-white rounded-full py-1 px-3 text-xs font-bold ml-3">
					<PaperAirplaneIcon class="w-3 h-3"></PaperAirplaneIcon>
					<span class="ml-2">VPN</span>
				</span>
			</div>
		</td>
		
		<td>
			<div class="flex items-center">
				<StopCircleIcon class="w-4 h-4"></StopCircleIcon>
				<a :href="`/session/end/${id}`" class="ml-2 text-sm">End session</a>
			</div>
		</td>
	</tr>
</template>

<style lang="scss" scoped>
td {
	@apply px-4 py-2;
}
</style>

<script setup lang="ts">
import { LanguageIcon, FingerPrintIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline';
import { StopCircleIcon } from '@heroicons/vue/24/solid';
import Flag from "vue-country-flag-next";

type User = {
	username: string
	avatar: string
};

type Location = {
	country: string
	city: string|undefined
};

type Session = {
	id: string,
	user?: User,
	location?: Location,
	language: string,
	ip: string,
	vpn: boolean
};

const props  = defineProps<Session>();

const emits = defineEmits([
	
]);

const _btoa = function (str: string) { return window.btoa(str); };

</script>
