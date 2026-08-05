<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#12275a] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0e1f48] focus:bg-[#0e1f48] active:bg-[#0a1738] focus:outline-none focus:ring-2 focus:ring-[#12275a] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
