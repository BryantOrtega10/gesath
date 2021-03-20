@if (sizeof($itemsMenu) > 0)
    <ul class="subMenu">
        @foreach ($itemsMenu as $item)
            <li>
                <a href="{{$item->link}}" >
                    <span class="textoMenu">{{$item->nombre}}</span>
                </a>
            </li>
        @endforeach    
    </ul>
@else
    <div class="itemsNoEncontrados">
        No hay resultados con esa busqueda
    </div>
@endif

