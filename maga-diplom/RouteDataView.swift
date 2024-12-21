//
//  RouteDataView.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 20.11.2024.
//

import SwiftUI
import CoreLocation

class RouteDataViewModel: ObservableObject {
    enum Event {
        case didTapSetStartPoint
        case didTapSetEndPoint
        case didTapNext
    }
    
    @Published var startPointText: String = "Set START point"
    @Published var endPointText: String = "Set END point"
    @Published var isNextButtonActive: Bool = false
    
    var eventHandler: ((Event) -> Void)?
    
    func setCoordinateAOnButton(_ coordinate: CLLocationCoordinate2D) {
        startPointText = "Lat: \(coordinate.latitude), Lon: \(coordinate.longitude)"
    }
    
    func setCoordinateBOnButton(_ coordinate: CLLocationCoordinate2D) {
        endPointText = "Lat: \(coordinate.latitude), Lon: \(coordinate.longitude)"
    }
    
    func setActiveBottomButton() {
        isNextButtonActive = true
    }
    
    func setDisabledBottomButton() {
        isNextButtonActive = false
    }
}

struct RouteDataView: View {
    @ObservedObject var viewModel: RouteDataViewModel
    
    var body: some View {
        List {
            Section {
                Button(action: {
                    viewModel.eventHandler?(.didTapSetStartPoint)
                }) {
                    HStack {
                        Text(viewModel.startPointText)
                        Spacer()
                        Image(systemName: "chevron.right")
                    }
                }
                
                Button(action: {
                    viewModel.eventHandler?(.didTapSetEndPoint)
                }) {
                    HStack {
                        Text(viewModel.endPointText)
                        Spacer()
                        Image(systemName: "chevron.right")
                    }
                }
            }
            
            Section {
                Button(action: {
                    viewModel.eventHandler?(.didTapNext)
                }) {
                    HStack {
                        Spacer()
                        Text("Next")
                            .foregroundColor(viewModel.isNextButtonActive ? .white : .gray)
                            .font(.system(size: 16, weight: .semibold))
                        Spacer()
                    }
                }
                .disabled(!viewModel.isNextButtonActive)
                .listRowBackground(
                    viewModel.isNextButtonActive ? Color.blue : Color.gray.opacity(0.1)
                )
            }
        }
    }
}

#Preview {
    RouteDataView(viewModel: .init())
}
