<template>
<div class ="p-6">
 <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-green-700">Panel de Coordinador</h1>
    <div class="flex gap-3 items-center">
        <Link href="/coordinador/feriantes/crear" class="bg-green-700 text-while text-sm px-3 py-2 rouned-lg">
        + Nuevo feriante
    </Link>
    <button @click="router.post('/logout')" class="text-sm text-gray-500 underline">Cerrar Sessión></button>
</div>
</div>

<p v-if="$page.props.flash?.success" class="bg-green-50 text-green-700 text-sm rouned-lg p-2 mb-4">
    {{$page.props.flash.success}}
</p>


<table class="w-full text-sm border-collapse">
    <thead>
        <tr class="bg-green-700 text-while">
            <th class="p-2 text-left">Nombre</th>
            <th class="p-2">Categoría</th>
            <th class="p-2">Puesto</th>
            <th class="p-2">Teléfono</th>
            <th class="p-2">Estado</th>
            <th class="p-2">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="f in feriantes" :key="f.id" class="border-b hover:bg-gray-50">
            <td class ="p-2">{{f.nombre}} {{f.apellido}}</td>
            <td class ="p-2 text-center">
                {{f.categoria?.nombre}}
                <span v-if="f.rubro_otro" class="text-gray-500">({{f.rubro_otro}})</span>
            </td>
            <td class="p-2 text-center">{{f.puesto?.numero ?? '-'}}</td>
            <td class="p-2 text-center">{{f.telefono}}</td>
            <td class="p-2 text-center">
                <span :class="{
                    'text-yellow-600':f.estado ==='pendiente',
                    'text-green-700' :f.estado ==='aprobado',
                    'text-red-500'   :f.estado ==='rechazado',
                }">{{f.estado}}</span>
                </td>
                <td class="p-2 text-center">
                    <div class="flex gap-1 justify-center flex-wrap">
                        <button @click="cambiarEstado(f.id,'aprobado')" class="bg-green-600 text-while px-2 py-1 rouned text-xs">Aprobar</button>
                        <button @click="cambiarEstado(f.id,'rechazado')" class="bg-red-500 text-while px-2 py-1 rouned text-xs">Rechazar</button>
                        <Link :href="`/coordinador/feriantes/${f.id}/editar`" class="bg-blue-500 text-while px-2 py-1 rouned text-xs">Editar</Link>
                        <button @click="eliminar(f.id)" class="bg-gray-700 text-while px-2 py-1 rouned text-xs">Eliminar</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    </template>

    <script setup>
    import {Link,router} from '@inertiajs/vue3'
    
    defineProps({
        feriantes:{
            type:Array,
            default:()=>[],
        },
    })

    function cambiarEstado(id,estado){
        router.patch(`/coordinador/feriantes/${id}/estado`,{ estado },{preserveScroll:true})
    }


    function eliminar(id){
        if (confirm('¿Eliminar este feriante? Se liberará su puesto.')){
            router.delete(`/coordinador/feriantes/${id}`,{preserveScroll:true})
        }
    }
    </script>

