@props([
    'member',
])

<li class="genealogy-node">
    <div class="node-content">
        <div class="node-main">
            <div class="node-avatar">
                {{-- Placeholder for an avatar --}}
                <div class="avatar-initials">{{ strtoupper(substr($member->username, 0, 1)) }}</div>
            </div>
            <div class="node-details">
                <div class="node-name">{{ $member->fullname }} <span class="node-username">({{ $member->username }})</span></div>
                <div class="node-meta">Joined: {{ date('M d, Y', strtotime($member->join_date)) }}</div>
            </div>
        </div>
        <div class="node-stats">
            <div class="node-level">Level {{ $member->level }}</div>
            <div class="node-status status-{{ $member->status }}">{{ ucfirst($member->status) }}</div>
        </div>
        @if(!empty($member->children))
            <span class="node-toggle" role="button" tabindex="0" aria-expanded="false">
                <svg class="icon icon-sm icon-zoom-in">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-zoom-in') }}"></use>
                </svg>
                <svg class="icon icon-sm icon-zoom-out" style="display: none;">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-zoom-out') }}"></use>
                </svg>
            </span>
        @endif
    </div>

    @if (!empty($member->children))
        <ul class="genealogy-level" style="display: none;">
            @foreach ($member->children as $child)
                <x-genealogy-node :member="$child" />
            @endforeach
        </ul>
    @endif
</li>

@once
<style>
    .genealogy-tree, .genealogy-level {
        list-style: none;
        padding-left: 0;
    }
    .genealogy-level {
        padding-left: 20px; /* Default indentation */
        border-left: 1px solid #ddd;
        margin-left: 10px;
    }
    .genealogy-node {
        margin-bottom: 10px;
    }
    .node-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 15px;
        flex-wrap: wrap; /* Allow wrapping on small screens */
        position: relative; /* For mobile toggle positioning */
    }
    .node-main {
        display: flex;
        align-items: center;
        flex-grow: 1;
    }
    .node-avatar {
        margin-right: 15px;
    }
    .avatar-initials {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #6c757d;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .node-name {
        font-weight: 600;
    }
    .node-username {
        color: #6c757d;
        font-weight: 400;
    }
    .node-meta {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .node-stats {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-left: 20px;
        flex-shrink: 0; /* Prevent stats from shrinking */
    }
    .node-level, .node-status {
        font-size: 0.85rem;
    }

    .node-status {
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 4px;
        color: white;
    }
    .status-active { background-color: #28a745; }
    .status-inactive { background-color: #6c757d; }
    .status-suspended { background-color: #dc3545; }

    .node-toggle {
        cursor: pointer;
        margin-left: 15px;
        color: #6c757d;
    }
    .node-toggle:hover {
        color: #3c4b64;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .node-content {
            flex-direction: column;
            align-items: flex-start;
        }
        .node-stats {
            margin-left: 0;
            margin-top: 10px;
            width: 100%;
            justify-content: space-between;
        }
        .genealogy-level {
            padding-left: 10px; /* Reduced indentation for mobile */
            margin-left: 5px;
        }
        .node-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    }
</style>
@endonce

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Search Functionality ---
    const searchInput = document.getElementById('genealogy-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const allNodes = document.querySelectorAll('.genealogy-node');

            allNodes.forEach(node => {
                const name = node.querySelector('.node-name').textContent.toLowerCase();
                const username = node.querySelector('.node-username').textContent.toLowerCase();
                const isMatch = name.includes(searchTerm) || username.includes(searchTerm);

                if (isMatch) {
                    // Show the node and all its parents
                    node.style.display = 'block';
                    let parent = node.parentElement.closest('.genealogy-node');
                    while(parent) {
                        parent.style.display = 'block';
                        // Also expand the parent to make the child visible
                        const parentToggle = parent.querySelector('.node-toggle');
                        const childrenList = parent.querySelector('.genealogy-level');
                        if(parentToggle && childrenList && parentToggle.getAttribute('aria-expanded') === 'false') {
                            parentToggle.click();
                        }
                        parent = parent.parentElement.closest('.genealogy-node');
                    }
                } else {
                    // Hide the node if it doesn't match
                    node.style.display = 'none';
                }
            });
        });
    }

    // --- Toggle Functionality ---
    document.body.addEventListener('click', function(e) {
        const toggle = e.target.closest('.node-toggle');
        if (toggle) {
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            const childrenList = toggle.closest('.genealogy-node').querySelector('.genealogy-level');
            const zoomInIcon = toggle.querySelector('.icon-zoom-in');
            const zoomOutIcon = toggle.querySelector('.icon-zoom-out');

            if (childrenList) {
                toggle.setAttribute('aria-expanded', !isExpanded);
                childrenList.style.display = isExpanded ? 'none' : 'block';
                
                if (zoomInIcon && zoomOutIcon) {
                    if (isExpanded) {
                        zoomInIcon.style.display = 'inline';
                        zoomOutIcon.style.display = 'none';
                    } else {
                        zoomInIcon.style.display = 'none';
                        zoomOutIcon.style.display = 'inline';
                    }
                }
            }
        }
    });
});
</script>
@endonce

