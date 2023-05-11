type User = {
	username: string;
	avatar: string;
};
type Location = {
	country: string;
	city: string | undefined;
};
type Session = {
	id: string;
	user?: User;
	location?: Location;
	language: string;
	ip: string;
	vpn: boolean;
};
declare const _default: import("vue").DefineComponent<__VLS_TypePropsToRuntimeProps<Session>, {}, unknown, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, never[], never, import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps, Readonly<import("vue").ExtractPropTypes<__VLS_TypePropsToRuntimeProps<Session>>> & {}, {}, {}>;
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
