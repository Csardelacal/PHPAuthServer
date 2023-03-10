declare const _default: import("vue").DefineComponent<__VLS_TypePropsToRuntimeProps<{
	id: string;
	user?: {
		username: string;
		avatar: string;
	};
	location?: {
		country: string;
		city: string | undefined;
	};
	language: string;
	ip: string;
	vpn: boolean;
}>, {}, unknown, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, any[], any, import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps, Readonly<import("vue").ExtractPropTypes<__VLS_TypePropsToRuntimeProps<{
	id: string;
	user?: {
		username: string;
		avatar: string;
	};
	location?: {
		country: string;
		city: string | undefined;
	};
	language: string;
	ip: string;
	vpn: boolean;
}>>> & {
	[x: `on${Capitalize<any>}`]: (...args: any[]) => any;
}, {}>;
export default _default;
type __VLS_NonUndefinedable<T> = T extends undefined ? never : T;
type __VLS_TypePropsToRuntimeProps<T> = {
	[K in keyof T]-?: {} extends Pick<T, K> ? {
		type: import('vue').PropType<__VLS_NonUndefinedable<T[K]>>;
	} : {
		type: import('vue').PropType<T[K]>;
		required: true;
	};
};
