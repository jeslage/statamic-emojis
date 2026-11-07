// Minimal stand-ins for Statamic's UI components (in the CP they come from Statamic at runtime)
import { defineComponent, h } from 'vue';

const wrapper = (name) => defineComponent({ name, setup: (_, { slots }) => () => h('div', { [`data-${name}`]: '' }, slots.default?.()) });

export const InputGroup = wrapper('input-group');
export const InputGroupPrepend = wrapper('input-group-prepend');
export const InputGroupAppend = wrapper('input-group-append');
export const CharacterCounter = defineComponent({
    name: 'CharacterCounter',
    props: { text: String, limit: Number },
    setup: (props) => () => h('span', { 'data-counter': '' }, `${props.text?.length ?? 0}/${props.limit}`),
});
export const Button = wrapper('button');
export const Popover = wrapper('popover');
export const Input = wrapper('input');
export const useContentDirection = () => ({ direction: 'ltr' });
