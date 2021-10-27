
(function(l, r) { if (!l || l.getElementById('livereloadscript')) return; r = l.createElement('script'); r.async = 1; r.src = '//' + (self.location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1'; r.id = 'livereloadscript'; l.getElementsByTagName('head')[0].appendChild(r) })(self.document);
var app = (function () {
    'use strict';

    function noop() { }
    function add_location(element, file, line, column, char) {
        element.__svelte_meta = {
            loc: { file, line, column, char }
        };
    }
    function run(fn) {
        return fn();
    }
    function blank_object() {
        return Object.create(null);
    }
    function run_all(fns) {
        fns.forEach(run);
    }
    function is_function(thing) {
        return typeof thing === 'function';
    }
    function safe_not_equal(a, b) {
        return a != a ? b == b : a !== b || ((a && typeof a === 'object') || typeof a === 'function');
    }
    function is_empty(obj) {
        return Object.keys(obj).length === 0;
    }
    function action_destroyer(action_result) {
        return action_result && is_function(action_result.destroy) ? action_result.destroy : noop;
    }
    function append(target, node) {
        target.appendChild(node);
    }
    function insert(target, node, anchor) {
        target.insertBefore(node, anchor || null);
    }
    function detach(node) {
        node.parentNode.removeChild(node);
    }
    function element(name) {
        return document.createElement(name);
    }
    function text(data) {
        return document.createTextNode(data);
    }
    function space() {
        return text(' ');
    }
    function listen(node, event, handler, options) {
        node.addEventListener(event, handler, options);
        return () => node.removeEventListener(event, handler, options);
    }
    function attr(node, attribute, value) {
        if (value == null)
            node.removeAttribute(attribute);
        else if (node.getAttribute(attribute) !== value)
            node.setAttribute(attribute, value);
    }
    function children(element) {
        return Array.from(element.childNodes);
    }
    function custom_event(type, detail, bubbles = false) {
        const e = document.createEvent('CustomEvent');
        e.initCustomEvent(type, bubbles, false, detail);
        return e;
    }

    let current_component;
    function set_current_component(component) {
        current_component = component;
    }

    const dirty_components = [];
    const binding_callbacks = [];
    const render_callbacks = [];
    const flush_callbacks = [];
    const resolved_promise = Promise.resolve();
    let update_scheduled = false;
    function schedule_update() {
        if (!update_scheduled) {
            update_scheduled = true;
            resolved_promise.then(flush);
        }
    }
    function add_render_callback(fn) {
        render_callbacks.push(fn);
    }
    let flushing = false;
    const seen_callbacks = new Set();
    function flush() {
        if (flushing)
            return;
        flushing = true;
        do {
            // first, call beforeUpdate functions
            // and update components
            for (let i = 0; i < dirty_components.length; i += 1) {
                const component = dirty_components[i];
                set_current_component(component);
                update(component.$$);
            }
            set_current_component(null);
            dirty_components.length = 0;
            while (binding_callbacks.length)
                binding_callbacks.pop()();
            // then, once components are updated, call
            // afterUpdate functions. This may cause
            // subsequent updates...
            for (let i = 0; i < render_callbacks.length; i += 1) {
                const callback = render_callbacks[i];
                if (!seen_callbacks.has(callback)) {
                    // ...so guard against infinite loops
                    seen_callbacks.add(callback);
                    callback();
                }
            }
            render_callbacks.length = 0;
        } while (dirty_components.length);
        while (flush_callbacks.length) {
            flush_callbacks.pop()();
        }
        update_scheduled = false;
        flushing = false;
        seen_callbacks.clear();
    }
    function update($$) {
        if ($$.fragment !== null) {
            $$.update();
            run_all($$.before_update);
            const dirty = $$.dirty;
            $$.dirty = [-1];
            $$.fragment && $$.fragment.p($$.ctx, dirty);
            $$.after_update.forEach(add_render_callback);
        }
    }
    const outroing = new Set();
    function transition_in(block, local) {
        if (block && block.i) {
            outroing.delete(block);
            block.i(local);
        }
    }

    const globals = (typeof window !== 'undefined'
        ? window
        : typeof globalThis !== 'undefined'
            ? globalThis
            : global);
    function mount_component(component, target, anchor, customElement) {
        const { fragment, on_mount, on_destroy, after_update } = component.$$;
        fragment && fragment.m(target, anchor);
        if (!customElement) {
            // onMount happens before the initial afterUpdate
            add_render_callback(() => {
                const new_on_destroy = on_mount.map(run).filter(is_function);
                if (on_destroy) {
                    on_destroy.push(...new_on_destroy);
                }
                else {
                    // Edge case - component was destroyed immediately,
                    // most likely as a result of a binding initialising
                    run_all(new_on_destroy);
                }
                component.$$.on_mount = [];
            });
        }
        after_update.forEach(add_render_callback);
    }
    function destroy_component(component, detaching) {
        const $$ = component.$$;
        if ($$.fragment !== null) {
            run_all($$.on_destroy);
            $$.fragment && $$.fragment.d(detaching);
            // TODO null out other refs, including component.$$ (but need to
            // preserve final state?)
            $$.on_destroy = $$.fragment = null;
            $$.ctx = [];
        }
    }
    function make_dirty(component, i) {
        if (component.$$.dirty[0] === -1) {
            dirty_components.push(component);
            schedule_update();
            component.$$.dirty.fill(0);
        }
        component.$$.dirty[(i / 31) | 0] |= (1 << (i % 31));
    }
    function init(component, options, instance, create_fragment, not_equal, props, append_styles, dirty = [-1]) {
        const parent_component = current_component;
        set_current_component(component);
        const $$ = component.$$ = {
            fragment: null,
            ctx: null,
            // state
            props,
            update: noop,
            not_equal,
            bound: blank_object(),
            // lifecycle
            on_mount: [],
            on_destroy: [],
            on_disconnect: [],
            before_update: [],
            after_update: [],
            context: new Map(options.context || (parent_component ? parent_component.$$.context : [])),
            // everything else
            callbacks: blank_object(),
            dirty,
            skip_bound: false,
            root: options.target || parent_component.$$.root
        };
        append_styles && append_styles($$.root);
        let ready = false;
        $$.ctx = instance
            ? instance(component, options.props || {}, (i, ret, ...rest) => {
                const value = rest.length ? rest[0] : ret;
                if ($$.ctx && not_equal($$.ctx[i], $$.ctx[i] = value)) {
                    if (!$$.skip_bound && $$.bound[i])
                        $$.bound[i](value);
                    if (ready)
                        make_dirty(component, i);
                }
                return ret;
            })
            : [];
        $$.update();
        ready = true;
        run_all($$.before_update);
        // `false` as a special case of no DOM component
        $$.fragment = create_fragment ? create_fragment($$.ctx) : false;
        if (options.target) {
            if (options.hydrate) {
                const nodes = children(options.target);
                // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                $$.fragment && $$.fragment.l(nodes);
                nodes.forEach(detach);
            }
            else {
                // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                $$.fragment && $$.fragment.c();
            }
            if (options.intro)
                transition_in(component.$$.fragment);
            mount_component(component, options.target, options.anchor, options.customElement);
            flush();
        }
        set_current_component(parent_component);
    }
    /**
     * Base class for Svelte components. Used when dev=false.
     */
    class SvelteComponent {
        $destroy() {
            destroy_component(this, 1);
            this.$destroy = noop;
        }
        $on(type, callback) {
            const callbacks = (this.$$.callbacks[type] || (this.$$.callbacks[type] = []));
            callbacks.push(callback);
            return () => {
                const index = callbacks.indexOf(callback);
                if (index !== -1)
                    callbacks.splice(index, 1);
            };
        }
        $set($$props) {
            if (this.$$set && !is_empty($$props)) {
                this.$$.skip_bound = true;
                this.$$set($$props);
                this.$$.skip_bound = false;
            }
        }
    }

    function dispatch_dev(type, detail) {
        document.dispatchEvent(custom_event(type, Object.assign({ version: '3.44.0' }, detail), true));
    }
    function append_dev(target, node) {
        dispatch_dev('SvelteDOMInsert', { target, node });
        append(target, node);
    }
    function insert_dev(target, node, anchor) {
        dispatch_dev('SvelteDOMInsert', { target, node, anchor });
        insert(target, node, anchor);
    }
    function detach_dev(node) {
        dispatch_dev('SvelteDOMRemove', { node });
        detach(node);
    }
    function listen_dev(node, event, handler, options, has_prevent_default, has_stop_propagation) {
        const modifiers = options === true ? ['capture'] : options ? Array.from(Object.keys(options)) : [];
        if (has_prevent_default)
            modifiers.push('preventDefault');
        if (has_stop_propagation)
            modifiers.push('stopPropagation');
        dispatch_dev('SvelteDOMAddEventListener', { node, event, handler, modifiers });
        const dispose = listen(node, event, handler, options);
        return () => {
            dispatch_dev('SvelteDOMRemoveEventListener', { node, event, handler, modifiers });
            dispose();
        };
    }
    function attr_dev(node, attribute, value) {
        attr(node, attribute, value);
        if (value == null)
            dispatch_dev('SvelteDOMRemoveAttribute', { node, attribute });
        else
            dispatch_dev('SvelteDOMSetAttribute', { node, attribute, value });
    }
    function validate_slots(name, slot, keys) {
        for (const slot_key of Object.keys(slot)) {
            if (!~keys.indexOf(slot_key)) {
                console.warn(`<${name}> received an unexpected slot "${slot_key}".`);
            }
        }
    }
    /**
     * Base class for Svelte components with some minor dev-enhancements. Used when dev=true.
     */
    class SvelteComponentDev extends SvelteComponent {
        constructor(options) {
            if (!options || (!options.target && !options.$$inline)) {
                throw new Error("'target' is a required option");
            }
            super();
        }
        $destroy() {
            super.$destroy();
            this.$destroy = () => {
                console.warn('Component was already destroyed'); // eslint-disable-line no-console
            };
        }
        $capture_state() { }
        $inject_state() { }
    }

    function ajaxify(form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            form.dispatchEvent(new CustomEvent('form-submitting', {event: e}));
            try {    
                const res = await fetch(form.getAttribute('action'), {
                    method: form.getAttribute('method'),
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                await form.dispatchEvent(new CustomEvent('form-submitted', {detail: {res}}));
            } catch (ex) {
                form.dispatchEvent(new CustomEvent('form-error', {detail: {exception: ex}}));
            }
        });
    }

    /* src/App.svelte generated by Svelte v3.44.0 */

    const { console: console_1 } = globals;
    const file = "src/App.svelte";

    // (32:3) {#if s}
    function create_if_block(ctx) {
    	let input;

    	const block = {
    		c: function create() {
    			input = element("input");
    			attr_dev(input, "type", "submit");
    			input.value = "Ajax Submit";
    			attr_dev(input, "class", "svelte-1qa57l8");
    			add_location(input, file, 32, 4, 612);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, input, anchor);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(input);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block.name,
    		type: "if",
    		source: "(32:3) {#if s}",
    		ctx
    	});

    	return block;
    }

    function create_fragment(ctx) {
    	let main;
    	let h10;
    	let t1;
    	let div0;
    	let h11;
    	let t3;
    	let form0;
    	let input0;
    	let t4;
    	let input1;
    	let t5;
    	let t6;
    	let div1;
    	let h12;
    	let t8;
    	let form1;
    	let input2;
    	let t9;
    	let input3;
    	let t10;
    	let input4;
    	let mounted;
    	let dispose;
    	let if_block = /*s*/ ctx[0] && create_if_block(ctx);

    	const block = {
    		c: function create() {
    			main = element("main");
    			h10 = element("h1");
    			h10.textContent = "FasterPhp + Svelte";
    			t1 = space();
    			div0 = element("div");
    			h11 = element("h1");
    			h11.textContent = "Ajax Form";
    			t3 = space();
    			form0 = element("form");
    			input0 = element("input");
    			t4 = space();
    			input1 = element("input");
    			t5 = space();
    			if (if_block) if_block.c();
    			t6 = space();
    			div1 = element("div");
    			h12 = element("h1");
    			h12.textContent = "Non Ajax Form";
    			t8 = space();
    			form1 = element("form");
    			input2 = element("input");
    			t9 = space();
    			input3 = element("input");
    			t10 = space();
    			input4 = element("input");
    			attr_dev(h10, "class", "svelte-1qa57l8");
    			add_location(h10, file, 18, 1, 306);
    			attr_dev(h11, "class", "svelte-1qa57l8");
    			add_location(h11, file, 20, 2, 343);
    			attr_dev(input0, "type", "text");
    			attr_dev(input0, "name", "first_name");
    			attr_dev(input0, "class", "svelte-1qa57l8");
    			add_location(input0, file, 29, 3, 515);
    			attr_dev(input1, "type", "text");
    			attr_dev(input1, "name", "last_name");
    			attr_dev(input1, "class", "svelte-1qa57l8");
    			add_location(input1, file, 30, 3, 558);
    			attr_dev(form0, "action", "");
    			attr_dev(form0, "method", "post");
    			attr_dev(form0, "enctype", "multipart/form-data");
    			add_location(form0, file, 21, 2, 364);
    			attr_dev(div0, "class", "svelte-1qa57l8");
    			add_location(div0, file, 19, 1, 335);
    			attr_dev(h12, "class", "svelte-1qa57l8");
    			add_location(h12, file, 38, 2, 693);
    			attr_dev(input2, "type", "text");
    			attr_dev(input2, "name", "first_name");
    			attr_dev(input2, "class", "svelte-1qa57l8");
    			add_location(input2, file, 40, 3, 782);
    			attr_dev(input3, "type", "text");
    			attr_dev(input3, "name", "last_name");
    			attr_dev(input3, "class", "svelte-1qa57l8");
    			add_location(input3, file, 41, 3, 825);
    			attr_dev(input4, "type", "submit");
    			attr_dev(input4, "class", "svelte-1qa57l8");
    			add_location(input4, file, 42, 3, 867);
    			attr_dev(form1, "action", "");
    			attr_dev(form1, "method", "post");
    			attr_dev(form1, "enctype", "multipart/form-data");
    			add_location(form1, file, 39, 2, 718);
    			attr_dev(div1, "class", "svelte-1qa57l8");
    			add_location(div1, file, 37, 1, 685);
    			attr_dev(main, "class", "svelte-1qa57l8");
    			add_location(main, file, 17, 0, 298);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, main, anchor);
    			append_dev(main, h10);
    			append_dev(main, t1);
    			append_dev(main, div0);
    			append_dev(div0, h11);
    			append_dev(div0, t3);
    			append_dev(div0, form0);
    			append_dev(form0, input0);
    			append_dev(form0, t4);
    			append_dev(form0, input1);
    			append_dev(form0, t5);
    			if (if_block) if_block.m(form0, null);
    			append_dev(main, t6);
    			append_dev(main, div1);
    			append_dev(div1, h12);
    			append_dev(div1, t8);
    			append_dev(div1, form1);
    			append_dev(form1, input2);
    			append_dev(form1, t9);
    			append_dev(form1, input3);
    			append_dev(form1, t10);
    			append_dev(form1, input4);

    			if (!mounted) {
    				dispose = [
    					action_destroyer(ajaxify.call(null, form0)),
    					listen_dev(form0, "form-submitting", /*before*/ ctx[1], false, false, false),
    					listen_dev(form0, "form-submitted", /*after*/ ctx[2], false, false, false)
    				];

    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*s*/ ctx[0]) {
    				if (if_block) ; else {
    					if_block = create_if_block(ctx);
    					if_block.c();
    					if_block.m(form0, null);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(main);
    			if (if_block) if_block.d();
    			mounted = false;
    			run_all(dispose);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('App', slots, []);
    	let s = true;

    	async function before(event) {
    		console.log("before", event);
    		$$invalidate(0, s = false);
    	}

    	async function after(event) {
    		const result = await event.detail.res;
    		const ret = await result.json();
    		alert(ret.message);
    		$$invalidate(0, s = true);
    	}

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console_1.warn(`<App> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({ ajaxify, s, before, after });

    	$$self.$inject_state = $$props => {
    		if ('s' in $$props) $$invalidate(0, s = $$props.s);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [s, before, after];
    }

    class App extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance, create_fragment, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "App",
    			options,
    			id: create_fragment.name
    		});
    	}
    }

    const app = new App({
    	target: document.body,
    	props: {
    		name: 'world'
    	}
    });

    return app;

})();
//# sourceMappingURL=bundle.js.map
