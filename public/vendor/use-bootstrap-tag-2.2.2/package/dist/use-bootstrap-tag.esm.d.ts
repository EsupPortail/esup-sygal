interface UseBootstrapTagReturnType {
	getValue: () => string;
	getValues: () => string[];
	addValue: (value: string | string[]) => void;
	removeValue: (value: string | string[]) => void;
}
export default function UseBootstrapTag(element: Element | HTMLElement | null): UseBootstrapTagReturnType;export {};
