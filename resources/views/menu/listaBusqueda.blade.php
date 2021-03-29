@if (sizeof($itemsMenu) > 0)
    <ul class="subMenu">
        @foreach ($itemsMenu as $item)
            @if ($item->link != "")
                <li>
                    <a href="{{$item->link}}" >
                        <span class="textoMenu">{{$item->nombre}}</span>
                    </a>
                </li>
            @endif            
        @endforeach    
    </ul>
@else
    <div class="itemsNoEncontrados">
        No hay resultados con esa busqueda
    </div>
@endif

