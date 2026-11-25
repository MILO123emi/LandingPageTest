<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Check, ChevronsUpDown, Store } from '@lucide/vue';
import { computed, onUnmounted, ref } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';

const page = usePage();
const adminSites = computed(() => page.props.adminSites);
const currentSite = computed(() => page.props.currentSite);

const open = ref(false);
const { setOpenMobile } = useSidebar();

const stopListening = router.on('navigate', () => {
    open.value = false;
    setOpenMobile(false);
});

onUnmounted(stopListening);
</script>

<template>
    <SidebarMenu v-if="adminSites.length > 1">
        <SidebarMenuItem>
            <DropdownMenu v-model:open="open">
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton size="lg">
                        <div class="flex size-8 items-center justify-center rounded-lg bg-primary text-sm font-semibold text-primary-foreground">
                            <span v-if="currentSite">{{ currentSite.name.charAt(0) }}</span>
                            <Store v-else class="size-4" aria-hidden="true" />
                        </div>
                        <div class="grid flex-1 text-left">
                            <span class="truncate font-medium">{{ currentSite?.name ?? 'Tus tiendas' }}</span>
                            <span class="text-xs text-muted-foreground">Cambiar tienda</span>
                        </div>
                        <ChevronsUpDown class="ml-auto size-4" aria-hidden="true" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="min-w-56" align="start">
                    <DropdownMenuLabel>Tus tiendas</DropdownMenuLabel>
                    <DropdownMenuItem v-for="site in adminSites" :key="site.slug" as-child>
                        <Link :href="site.adminUrl" :aria-current="site.slug === currentSite?.slug ? 'page' : undefined">
                            <Check class="size-4" :class="{ invisible: site.slug !== currentSite?.slug }" aria-hidden="true" />
                            {{ site.name }}
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem as-child>
                        <Link :href="dashboard()">Ver todas</Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
