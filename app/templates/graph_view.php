<?php
// app/templates/graph_view.php
?>

<div class="p-6 bg-white rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-indigo-700 mb-4">Your Course Dependency Graph</h2>
    <p class="text-gray-600 mb-4">Click on a course node for more details. Drag nodes to rearrange the graph. Scroll to zoom.</p>

    <div id="loadingMessage" class="text-center text-gray-500 py-8">Loading graph data...</div>
    <div id="network-container" class="border border-gray-300 rounded-lg" style="width: 100%; height: 600px;"></div>

</div>

<!-- Include vis.js library -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const loadingMessage = document.getElementById('loadingMessage');
        const container = document.getElementById('network-container');

        // Check if the container element exists
        if (!container) {
            console.error("Network container not found!");
            if (loadingMessage) loadingMessage.textContent = "Error: Graph container not found.";
            return;
        }

        // Fetch graph data from our API endpoint
        fetch('api/courses_graph_data.php')
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        return Promise.reject('Unauthorized. Please log in.');
                    }
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (loadingMessage) loadingMessage.style.display = 'none'; // Hide loading message

                if (data.error) {
                    if (loadingMessage) {
                        loadingMessage.style.display = 'block';
                        loadingMessage.textContent = 'Error loading graph: ' + data.message;
                    } else {
                        alert('Error: ' + data.message);
                    }
                    return;
                }

                if (data.nodes.length === 0) {
                     if (loadingMessage) {
                        loadingMessage.style.display = 'block';
                        loadingMessage.textContent = "No courses found to display a graph. Add some courses first!";
                    }
                    return;
                }

                // Create a DataSet for nodes and edges
                const nodes = new vis.DataSet(data.nodes);
                const edges = new vis.DataSet(data.edges);

                // Provide the data to the Network
                const graphData = {
                    nodes: nodes,
                    edges: edges
                };

                // Define options for the network visualization
                const options = {
                    physics: {
                        enabled: true,
                        // You can experiment with different physics layouts:
                        // solver: 'barnesHut',
                        // barnesHut: {
                        //     gravitationalConstant: -2000,
                        //     centralGravity: 0.3,
                        //     springLength: 95,
                        //     springConstant: 0.04,
                        //     damping: 0.09,
                        //     avoidOverlap: 0
                        // },
                        stabilization: {
                            enabled: true,
                            iterations: 1000, // Number of iterations to stabilize the physics simulation
                            updateInterval: 25 // How often to update the view during stabilization
                        }
                    },
                    edges: {
                        color: { inherit: 'from' },
                        arrows: 'to',
                        smooth: {
                            enabled: true,
                            type: "dynamic"
                        }
                    },
                    nodes: {
                        shape: 'box', // or 'dot', 'ellipse', 'circle', 'text'
                        size: 20,
                        font: {
                            size: 14,
                            color: '#333',
                            face: 'Inter, sans-serif'
                        },
                        borderWidth: 2,
                        shadow: {
                            enabled: true,
                            color: 'rgba(0,0,0,0.3)',
                            size: 8,
                            x: 5,
                            y: 5
                        },
                        scaling: {
                            min: 10,
                            max: 30
                        }
                    },
                    groups: {
                        // Define colors for different departments
                        'Mathematics': { color: { background: '#A7F3D0', border: '#10B981' } }, // Green
                        'Software Technologies': { color: { background: '#BFDBFE', border: '#3B82F6' } }, // Blue
                        'Informatics': { color: { background: '#FDE68A', border: '#F59E0B' } }, // Yellow
                        'Database': { color: { background: '#FBCFE8', border: '#EC4899' } }, // Pink
                        'English': { color: { background: '#FED7AA', border: '#F97316' } }, // Orange
                        'Soft Skills': { color: { background: '#E0E7FF', border: '#6366F1' } }, // Purple
                        'Other': { color: { background: '#E5E7EB', border: '#6B7280' } } // Gray
                    },
                    interaction: {
                        hover: true,
                        tooltipDelay: 300,
                        zoomView: true,
                        dragNodes: true,
                        dragView: true
                    }
                };

                // Initialize the Network
                const network = new vis.Network(container, graphData, options);

                // Optional: Add a click event listener for nodes
                network.on("click", function (params) {
                    if (params.nodes.length > 0) {
                        const nodeId = params.nodes[0];
                        const clickedNode = nodes.get(nodeId);
                        // alert(`Course: ${clickedNode.title}\nCode: ${clickedNode.label}\nDepartment: ${clickedNode.group}`);
                        // You could expand this to show a custom modal with more details
                    }
                });

            })
            .catch(error => {
                console.error('Error fetching or processing graph data:', error);
                if (loadingMessage) {
                    loadingMessage.style.display = 'block';
                    loadingMessage.textContent = 'Failed to load graph data. ' + error;
                }
            });
    });
</script>
