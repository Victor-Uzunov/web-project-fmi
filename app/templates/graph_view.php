<?php
// app/templates/graph_view.php
?>

<div class="p-6 bg-white rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-indigo-700 mb-4">Your Course Dependency Graph</h2>
    <p class="text-gray-600 mb-4">Click on a course node for more details. Drag nodes to rearrange the graph. Scroll to zoom.</p>

    <div id="loadingMessage" class="text-center text-gray-500 py-8">Loading graph data...</div>
    <div id="network-container" class="border border-gray-300 rounded-lg" style="width: 100%; height: 600px;"></div>

</div>

<!-- Course Details Modal -->
<div id="courseDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm rounded-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-indigo-700" id="modalCourseName"></h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 text-3xl font-bold" onclick="closeCourseDetailsModal()">
                &times;
            </button>
        </div>
        <div class="space-y-2 text-gray-700">
            <p><span class="font-semibold">Course Code:</span> <span id="modalCourseCode"></span></p>
            <p><span class="font-semibold">Department:</span> <span id="modalDepartment"></span></p>
            <p><span class="font-semibold">Credits:</span> <span id="modalCredits"></span></p>
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeCourseDetailsModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Include vis.js library -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const loadingMessage = document.getElementById('loadingMessage');
        const container = document.getElementById('network-container');
        const courseDetailsModal = document.getElementById('courseDetailsModal');

        if (!container) {
            console.error("Network container not found!");
            if (loadingMessage) loadingMessage.textContent = "Error: Graph container not found.";
            return;
        }

        window.openCourseDetailsModal = function(nodeData) {
            document.getElementById('modalCourseName').textContent = nodeData.courseName;
            document.getElementById('modalCourseCode').textContent = nodeData.courseCode;
            document.getElementById('modalDepartment').textContent = nodeData.department;
            document.getElementById('modalCredits').textContent = nodeData.credits;
            courseDetailsModal.classList.remove('hidden');
            courseDetailsModal.classList.add('flex');
        };

        window.closeCourseDetailsModal = function() {
            courseDetailsModal.classList.add('hidden');
            courseDetailsModal.classList.remove('flex');
        };

        if (courseDetailsModal) {
            courseDetailsModal.addEventListener('click', (event) => {
                if (event.target === courseDetailsModal) {
                    closeCourseDetailsModal();
                }
            });
        }

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
                if (loadingMessage) loadingMessage.style.display = 'none';

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

                const nodes = new vis.DataSet(data.nodes);
                const edges = new vis.DataSet(data.edges);

                const graphData = {
                    nodes: nodes,
                    edges: edges
                };

                const options = {
                    physics: {
                        enabled: true,
                        solver: 'barnesHut',
                        barnesHut: {
                            gravitationalConstant: -2000,
                            centralGravity: 0.3,
                            springLength: 200,
                            springConstant: 0.05,
                            damping: 0.09,
                            avoidOverlap: 0
                        },
                        stabilization: {
                            enabled: true,
                            iterations: 1000,
                            updateInterval: 25
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
                        shape: 'box',
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

                const network = new vis.Network(container, graphData, options);

                network.on("click", function (params) {
                    if (params.nodes.length > 0) {
                        const nodeId = params.nodes[0];
                        const clickedNode = nodes.get(nodeId);
                        if (clickedNode) {
                            openCourseDetailsModal(clickedNode);
                        }
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
